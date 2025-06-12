<?php
declare(strict_types=1);

namespace App\Controllers\API;

use App\DTO\Todo\TaskDTO;
use App\Entities\Task;
use App\Entities\User;
use App\Exceptions\ServerErrorException;
use App\Middlewares\AuthMiddleware;
use App\Requests\Todo\AllRequest;
use App\Requests\Todo\CreateOrUpdateRequest;
use App\Requests\Todo\DeleteRequest;
use App\Requests\Todo\GetRequest;
use App\Requests\Todo\UpdateStatusRequest;
use App\Responses\Todo\TodoResponse;
use App\Services\Todo\TodoService;
use Beauty\Http\Middleware\Middleware;
use Beauty\Http\Request\HttpRequest;
use Beauty\Core\Router\Route;
use Beauty\Http\Enums\HttpMethodsEnum;
use Beauty\Http\Response\Contracts\ResponsibleInterface;
use Beauty\Http\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Middleware([AuthMiddleware::class])]
class TodoController
{
    /**
     * @param TodoService $todoService
     */
    public function __construct(
        protected TodoService $todoService,
    )
    {
    }

    /**
     * @param AllRequest $request
     * @return ResponseInterface
     */
    #[Route(method: HttpMethodsEnum::GET, path: '/api/todos')]
    public function all(AllRequest $request): ResponseInterface
    {
        $user = $request->getUser();

        $todos = $this->todoService->allByUserId($user->getId());

        return new JsonResponse(200, [
            'todos' => array_map(fn (Task $todo) => new TodoResponse(
                $todo->getId(),
                $todo->getTitle(),
                $todo->getDescription(),
                $todo->isCompleted(),
                $todo->getDueDate(),
            ), $todos),
        ]);
    }

    /**
     * @param GetRequest $request
     * @param int $id
     * @return ResponsibleInterface
     */
    #[Route(method: HttpMethodsEnum::GET, path: '/api/todo/{id}')]
    public function get(GetRequest $request, int $id): ResponsibleInterface
    {
        $user = $request->getUser();

        return $this->getTodoResponse($this->todoService->get($id, $user->getId()));
    }

    /**
     * @param CreateOrUpdateRequest $request
     * @return ResponsibleInterface
     * @throws \DateMalformedStringException
     */
    #[Route(method: HttpMethodsEnum::POST, path: '/api/todo')]
    public function create(CreateOrUpdateRequest $request): ResponsibleInterface
    {
        $user = $request->getUser();

        $dto = $this->getTodoDTO($request, $user);

        return $this->getTodoResponse($this->todoService->create($dto));
    }

    /**
     * @param CreateOrUpdateRequest $request
     * @param int $id
     * @return ResponsibleInterface
     * @throws ServerErrorException
     * @throws \DateMalformedStringException
     */
    #[Route(method: HttpMethodsEnum::PUT, path: '/api/todo/{id}')]
    public function update(CreateOrUpdateRequest $request, int $id): ResponsibleInterface
    {
        $user = $request->getUser();

        $dto = $this->getTodoDTO($request, $user);

        return $this->getTodoResponse($this->todoService->update($id, $dto));
    }

    /**
     * @param DeleteRequest $request
     * @param int $id
     * @return ResponseInterface
     * @throws ServerErrorException
     */
    #[Route(method: HttpMethodsEnum::DELETE, path: '/api/todo/{id}')]
    public function delete(DeleteRequest $request, int $id): ResponseInterface
    {
        $user = $request->getUser();

        $this->todoService->delete($id, $user->getId());

        return new JsonResponse(200, ['message' => 'Task deleted']);
    }

    /**
     * @param UpdateStatusRequest $request
     * @param int $id
     * @return ResponseInterface
     * @throws ServerErrorException
     */
    #[Route(method: HttpMethodsEnum::PATCH, path: '/api/todo/{id}/update-status')]
    public function updateStatus(UpdateStatusRequest $request, int $id): ResponseInterface
    {
        $user = $request->getUser();

        $isCompleted = $this->todoService->updateStatus($id, $user->getId(), $request->json('is_completed'));

        return new JsonResponse(200, ['is_completed' => $isCompleted]);
    }

    /**
     * @param HttpRequest $request
     * @param User $user
     * @return TaskDTO
     * @throws \DateMalformedStringException
     */
    private function getTodoDTO(HttpRequest $request, User $user): TaskDTO
    {
        return new TaskDTO(
            userId: $user->getId(),
            title: $request->json('title'),
            description: $request->json('description'),
            dueDate: new \DateTimeImmutable($request->json('due_date')),
            isCompleted: $request->json('is_completed') ?? false,
        );
    }

    /**
     * @param Task $todo
     * @return TodoResponse
     */
    private function getTodoResponse(Task $todo): TodoResponse
    {
        return new TodoResponse(
            $todo->getId(),
            $todo->getTitle(),
            $todo->getDescription(),
            $todo->isCompleted(),
            $todo->getDueDate(),
        );
    }
}