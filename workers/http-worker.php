<?php

use Beauty\Core\Kernel\App;
use Beauty\Core\Router\Exceptions\NotFoundException;
use Beauty\Http\Request\Exceptions\ValidationException;
use Beauty\Http\Response\ErrorResponse;
use Beauty\Http\Response\JsonResponse;
use Beauty\Http\Response\ValidationResponse;
use Nyholm\Psr7\Factory\Psr17Factory;
use Spiral\RoadRunner\Http\PSR7Worker;

$application = require __DIR__ . '/../bootstrap/kernel.php';

$psrFactory = new Psr17Factory();
$worker = new PSR7Worker(
    \Spiral\RoadRunner\Worker::create(),
    $psrFactory,
    $psrFactory,
    $psrFactory
);

$app = (new App(container: $application->containerManager->getContainer()))
    ->withRouterConfig($application->routerConfig)
    ->withMiddlewares($application->middlewares);

while ($psrRequest = $worker->waitRequest()) {
    try {
        $request = new \Beauty\Http\Request\HttpRequest(
            $psrRequest->getMethod(),
            (string) $psrRequest->getUri(),
            $psrRequest->getHeaders(),
            $psrRequest->getBody(),
            $psrRequest->getProtocolVersion(),
            $psrRequest->getServerParams()
        );

        $request = $request
            ->withQueryParams($psrRequest->getQueryParams())
            ->withParsedBody($psrRequest->getParsedBody())
            ->withUploadedFiles($psrRequest->getUploadedFiles())
            ->withCookieParams($psrRequest->getCookieParams());

        $response = $app->handle($request);
        $worker->respond($response);
    } catch (NotFoundException $exception) {
        $worker->respond(new JsonResponse(404, new ErrorResponse($exception->getMessage())));
    } catch (ValidationException $exception) {
        $worker->respond(new JsonResponse(400, new ValidationResponse($exception->getMessage(), $exception->getFails())));
    } catch (\App\Exceptions\UnauthorizedException $exception) {
        $worker->respond(new JsonResponse(401, new ErrorResponse($exception->getMessage())));
    } catch (\App\Exceptions\ConflictHttpException $exception) {
        $worker->respond(new JsonResponse(409, new ErrorResponse($exception->getMessage())));
    } catch (Throwable $e) {
        $trace = null;
        if (env('APP_DEBUG')) {
            $trace = [
                'previous' => $e->getPrevious(),
                'trace' => $e->getTrace(),
            ];
        }

        $worker->respond(new JsonResponse(500, new ErrorResponse($e->getMessage(), $trace)));
        $worker->getWorker()->error((string)$e);
    }
}
