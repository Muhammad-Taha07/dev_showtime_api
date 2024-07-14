<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\DB;
use App\Http\Responses\BaseResponse;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
    public function register()
    {
        $this->renderable(function (Throwable $e) {
            if (request()->is("api/*")) {
                $httpCode = $statusCode = $msg = "";

                $httpCode = $statusCode = match (class_basename($e)) {
                    "NotFoundHttpException" => 404,
                    "MethodNotAllowedHttpException" => 405,
                    "AuthenticationException", "AuthorizationException" => 401,
                    "QueryException" => 500,
                    "HttpResponseException" => 403,
                    default => 500
                };

                $msg = match (class_basename($e)) {
                    "NotFoundHttpException" => ($e->getMessage() == "") ? "Invalid Route Requested" : "Requested resource not found",
                    "MethodNotAllowedHttpException" => "Invalid method Used",
                    "AuthenticationException" => "Invalid or Expired Token",
                    "AuthorizationException" => "Access Denied",
                    "QueryException" => "SQL Error",
                    "HttpResponseException" => "You are not authorized to perform this operation",
                    default => "Internal Server Error",
                };

                DB::rollback();
                return new BaseResponse($httpCode, $statusCode, $e->getMessage() ?? $msg, []);
            }
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($request->is('api/*')) {
            if (
                $exception instanceof ValidationException
            ) {
                return $this->customException($exception, $request);
            }
        }

        return parent::render($request, $exception);
    }

    protected function customException(ValidationException $e)
    {
        if (request()->is('api/*')) {
            DB::rollback();

            return new BaseResponse(
                STATUS_CODE_BADREQUEST,
                STATUS_CODE_BADREQUEST,
                $e->validator->errors()->first()
            );
        }
    }
}
