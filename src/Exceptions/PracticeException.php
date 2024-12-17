<?php

namespace Caleb\Practice\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class PracticeException extends Exception
{
    protected mixed $data;

    public function __construct(string $message = '', int $code = Response::HTTP_INTERNAL_SERVER_ERROR, mixed $data = null, ?\Throwable $previous = null)
    {
        $this->data = $data;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
