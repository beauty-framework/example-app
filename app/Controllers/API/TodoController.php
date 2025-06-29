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
use Beauty\Core\Router\Exceptions\NotFoundException;
use Beauty\Http\Middleware\Middleware;
use Beauty\Http\Request\HttpRequest;
use Beauty\Core\Router\Route;
use Beauty\Http\Enums\HttpMethodsEnum;
use Beauty\Http\Response\Contracts\ResponsibleInterface;
use Beauty\Http\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OAT;
use Psr\SimpleCache\InvalidArgumentException;

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
     * @throws InvalidArgumentException
     */
    #[Route(method: HttpMethodsEnum::GET, path: '/api/todos')]
    #[OAT\Get(
        path: '/todos',
        summary: 'All Tasks',
        security: ['bearerToken' => []],
        tags: ['Tasks'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful response',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(
                            property: 'todos',
                            type: 'array',
                            items: new OAT\Items(ref: '#/components/schemas/TodoResponse')
                        )
                    ],
                    type: 'object',
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'Not Authorized',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/ServerError')
                )
            ),
            new OAT\Response(
                response: 500,
                description: 'Server error',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/ServerError')
                )
            ),
        ],
    )]
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
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    #[Route(method: HttpMethodsEnum::GET, path: '/api/todo/{id}')]
    #[OAT\Get(
        path: '/todo/{id}',
        summary: 'Task by ID',
        security: ['bearerToken' => []],
        tags: ['Tasks'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'ID of the task',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer')
            )
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful response',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/TodoResponse')
                )
            ),
            new OAT\Response(
                response: 404,
                description: 'Not Found',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/ServerError')
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'Not Authorized',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/ServerError')
                )
            ),
            new OAT\Response(
                response: 500,
                description: 'Server error',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/ServerError')
                )
            ),
        ],
    )]
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
    #[OAT\Post(
        path: '/todo',
        summary: 'Create Task',
        security: ['bearerToken' => []],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\MediaType(
                mediaType: 'application/json',
                schema: new OAT\Schema(ref: '#/components/schemas/TodoRequest')
            )
        ),
        tags: ['Tasks'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful response',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/TodoResponse')
                )
            ),
            new OAT\Response(
                response: 400,
                description: 'Validation Error',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/AuthError')
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'Not Authorized',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/ServerError')
                )
            ),
            new OAT\Response(
                response: 500,
                description: 'Server error',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/ServerError')
                )
            ),
        ],
    )]
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
    #[OAT\Put(
        path: '/todo/{id}',
        summary: 'Update Task',
        security: ['bearerToken' => []],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\MediaType(
                mediaType: 'application/json',
                schema: new OAT\Schema(ref: '#/components/schemas/TodoRequest')
            )
        ),
        tags: ['Tasks'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'ID of the task',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer')
            )
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful response',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/TodoResponse')
                )
            ),
            new OAT\Response(
                response: 400,
                description: 'Validation Error',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/AuthError')
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'Not Authorized',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/ServerError')
                )
            ),
            new OAT\Response(
                response: 404,
                description: 'Not Found',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/ServerError')
                )
            ),
            new OAT\Response(
                response: 500,
                description: 'Server error',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/ServerError')
                )
            ),
        ],
    )]
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
    #[OAT\Delete(
        path: '/todo/{id}',
        summary: 'Delete Task',
        security: ['bearerToken' => []],
        tags: ['Tasks'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'ID of the task',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer')
            )
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Task deleted',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Task deleted')
                    ],
                    type: 'object'
                )
            ),
            new OAT\Response(
                response: 404,
                description: 'Not Found',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/ServerError')
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'Not Authorized',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/ServerError')
                )
            ),
            new OAT\Response(
                response: 500,
                description: 'Server error',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/ServerError')
                )
            ),
        ],
    )]
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
    #[OAT\Patch(
        path: '/todo/{id}/update-status',
        summary: 'Update Task status',
        security: ['bearerToken' => []],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\MediaType(
                mediaType: 'application/json',
                schema: new OAT\Schema(ref: '#/components/schemas/TodoStatusRequest')
            )
        ),
        tags: ['Tasks'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'ID of the task',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer')
            )
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful response',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/TodoResponse')
                )
            ),
            new OAT\Response(
                response: 400,
                description: 'Validation Error',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/AuthError')
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'Not Authorized',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/ServerError')
                )
            ),
            new OAT\Response(
                response: 404,
                description: 'Not Found',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/ServerError')
                )
            ),
            new OAT\Response(
                response: 500,
                description: 'Server error',
                content: new OAT\MediaType(
                    mediaType: 'application/json',
                    schema: new OAT\Schema(ref: '#/components/schemas/ServerError')
                )
            ),
        ],
    )]
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