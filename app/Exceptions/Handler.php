<?php

namespace App\Exceptions;

use App\Traits\TelegramSystemLogTrait;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use TelegramSystemLogTrait;
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        $prepareException = $this->prepareException($e);
        if (
            !$this->shouldReport($e)
            || !$this->shouldReport($prepareException)
            || $e instanceof ThrottleRequestsException
            || $prepareException instanceof ThrottleRequestsException
            // TODO: maybe shouldReport do all of this
            || $prepareException instanceof HttpResponseException
            || $prepareException instanceof AuthenticationException
            || $prepareException instanceof ValidationException
            || $prepareException instanceof NotFoundHttpException
            || $prepareException instanceof TokenMismatchException
            || $e instanceof TokenMismatchException
            || $prepareException instanceof MethodNotAllowedHttpException
        ) {
            return parent::render($request, $e);
        }
        $this->handleException($e);
//        if ($request->inertia() && !$request->expectsJson()) {
//            return redirect()
//                ->back()
//                ->with(['message_error' => $e->getMessage()]);
//        }
        return parent::render($request, $e);
    }
}
