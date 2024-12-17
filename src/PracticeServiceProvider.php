<?php

namespace Caleb\Practice;

use Caleb\Practice\Commands\FilterMakeCommand;
use Caleb\Practice\Commands\ServiceMakeCommand;
use Caleb\Practice\Exceptions\PracticeAppException;
use Caleb\Practice\Exceptions\PracticeException;
use Caleb\Practice\Http\Middleware\AddContext;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Response as HttpStatus;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class PracticeServiceProvider extends ServiceProvider
{
    /**
     * @return void
     * @author Caleb 2024/12/15
     */
    public function register()
    {

    }

    /**
     * @return void
     * @throws BindingResolutionException
     * @author Caleb 2024/12/16
     */
    public function boot()
    {
        $this->registerMiddleware();

        $this->registerExceptionHandler();

        $this->registerLang();

        $this->registerCommand();

        $this->registerStubs();

    }

    /**
     * @return void
     * @author Caleb 2024/12/17
     */
    public function registerLang()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'practice');
    }

    /**
     * @return void
     * @author Caleb 2024/12/17
     */
    public function registerCommand()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                FilterMakeCommand::class,
                ServiceMakeCommand::class,
            ]);
        }
    }

    /**
     * @return void
     * @author Caleb 2024/12/15
     */
    public function registerExceptionHandler()
    {
        $this->app->afterResolving(Handler::class, function ($handler) {
            $exceptions = new Exceptions($handler);

            $exceptions->level(PracticeAppException::class, LogLevel::DEBUG);

            $exceptions->render(function (Throwable $e) {
                $jsonResponse = new class {
                    use Response;
                };

                switch ($e) {
                    case $e instanceof ValidationException:
                        return $jsonResponse->error($e->getMessage(), HttpStatus::HTTP_UNPROCESSABLE_ENTITY, $e->errors());
                    case $e instanceof ThrottleRequestsException:
                    case $e instanceof AccessDeniedHttpException:
                        return $jsonResponse->error($e->getMessage(), $e->getStatusCode());
                    case $e instanceof AuthenticationException:
                        return $jsonResponse->error($e->getMessage(), HttpStatus::HTTP_UNAUTHORIZED);
                    case $e instanceof PracticeException:
                        return $jsonResponse->error($e->getMessage(), $e->getCode(), $e->getData());
                    case $e instanceof NotFoundHttpException:
                        if ($e->getPrevious() instanceof ModelNotFoundException) {
                            return $jsonResponse->error(trans('practice::messages.resource.not_found'), $e->getStatusCode());
                        }
                        return $jsonResponse->error($e->getMessage(), $e->getStatusCode());
                    default:
                        if (!config('app.debug')) {
                            // 不暴露系统错误
                            return $jsonResponse->error(trans('practice::messages.system.error'));
                        }
                }
            });
        });
    }

    /**
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @author Caleb 2024/12/16
     */
    public function registerMiddleware()
    {
        $this->app->make(HttpKernel::class)->prependMiddleware(AddContext::class);
    }

    /**
     * @return void
     * @author Caleb 2024/12/17
     */
    public function registerStubs()
    {
        $this->publishes([__DIR__ . '/Commands/stubs' => base_path('stubs')], ['sail', 'practice-stubs']);
    }
}
