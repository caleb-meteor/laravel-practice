<?php

namespace Caleb\Practice;

use Caleb\Practice\Exceptions\PracticeAppException;
use Caleb\Practice\Exceptions\ExternalAppException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

trait ThrowException
{
    /**
     * @return void
     *
     * @throws PracticeAppException
     */
    public function throwAppException(string $msg = '', int $code = Response::HTTP_SERVICE_UNAVAILABLE, mixed $data = null, ?Throwable $previous = null)
    {
        throw new PracticeAppException($msg ?: trans('practice::message.system.error'), $code, $data, $previous);
    }

    /**
     * @return mixed
     *
     * @throws ExternalAppException
     */
    public function throwExternalAppException(string $msg = '', int $code = Response::HTTP_SERVICE_UNAVAILABLE, mixed $data = null, ?Throwable $previous = null)
    {
        throw new ExternalAppException($msg ?: trans('practice::message.system.error'), $code, $data, $previous);
    }
}
