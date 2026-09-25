<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
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
        if ($e instanceof TokenMismatchException) {
            $redirect = $request->is('admin/login', 'admin/setup')
                ? redirect()->route('login')
                : redirect()->back();

            return $redirect
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'La session a expiré. La page a été rechargée, vous pouvez réessayer.');
        }

        return parent::render($request, $e);
    }
}
