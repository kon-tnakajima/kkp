<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if($request->expectsJson()) {
            if (basename($request->url()) === 'list') {
                return response()->json([
                        'table_name'    => $request->table_name ?? null,
                        'last_date'     => $request->last_date ?? null,
                        'idf'           => $request->idf ?? 0,
                        'idt'           => $request->idt ?? 0,
                        'count'         => 0,
                        'error_message' => 'Unauthenticated.',
                        'details'       => []
                    ], 401);
            } elseif (basename($request->url()) === 'store' || basename($request->url()) === 'file') {
                return response()->json([
                        'id'            => 0,
                        'other_storage' => '',
                        'status'        => 'failed',
                        'error_message' => 'Unauthenticated.'
                    ], 401);
            }
            return response()->json(['error_message' => 'Unauthenticated.'], 401);
        }
        return redirect()->guest(route('login'));
    }
}
