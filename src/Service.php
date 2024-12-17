<?php

namespace Caleb\Practice;


abstract class Service
{
    use ThrowException;

    public static function instance(): static
    {
        return app(static::class);
    }
}
