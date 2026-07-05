<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $apiError = function (string $message, int $status, mixed $errors = null) {
            $response = [
                'success' => false,
                'message' => $message,
            ];

            if (! is_null($errors)) {
                $response['errors'] = $errors;
            }

            return response()->json($response, $status);
        };

        $exceptions->render(function (ValidationException $exception, Request $request) use ($apiError) {
            if (! $request->is('api/*')) {
                return null;
            }

            return $apiError(
                $exception->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $exception->errors(),
            );
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) use ($apiError) {
            if (! $request->is('api/*')) {
                return null;
            }

            return $apiError(
                $exception->getMessage() ?: 'Unauthenticated.',
                Response::HTTP_UNAUTHORIZED,
            );
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) use ($apiError) {
            if (! $request->is('api/*')) {
                return null;
            }

            return $apiError(
                $exception->getMessage() ?: 'This action is unauthorized.',
                Response::HTTP_FORBIDDEN,
            );
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) use ($apiError) {
            if (! $request->is('api/*')) {
                return null;
            }

            return $apiError(
                'Resource not found.',
                Response::HTTP_NOT_FOUND,
            );
        });

        $exceptions->render(function (Throwable $exception, Request $request) use ($apiError) {
            if (! $request->is('api/*')) {
                return null;
            }

            if ($exception instanceof HttpExceptionInterface) {
                $message = match (true) {
                    $exception->getPrevious() instanceof AuthorizationException => 'This action is unauthorized.',
                    $exception->getPrevious() instanceof ModelNotFoundException => 'Resource not found.',
                    default => Response::$statusTexts[$exception->getStatusCode()] ?? 'Request failed.',
                };

                return $apiError(
                    $message,
                    $exception->getStatusCode(),
                );
            }

            return $apiError(
                'Something went wrong.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        });
    })->create();
