<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof \PDOException || $e instanceof \Illuminate\Database\QueryException) {
            // Check if DB is down or connection refused
            try {
                \DB::connection()->getPdo();
            } catch (\Exception $dbException) {
                // Database is definitely unreachable or access is denied
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Layanan database kami sedang mengalami gangguan. Silakan coba beberapa saat lagi.'
                    ], 500);
                }

                return response()->view('errors.db_error', [
                    'message' => $dbException->getMessage()
                ], 500);
            }
        }

        if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException || $e instanceof \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException) {
            if ($request->expectsJson() || $request->is('portal/*') || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Terlalu banyak permintaan. Coba lagi dalam beberapa saat.'
                ], 429);
            }
        }

        return parent::render($request, $e);
    }
}
