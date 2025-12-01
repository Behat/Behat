<?php

declare(strict_types=1);

class SharedServiceWithFactory extends SharedService
{
    private function __construct()
    {
    }

    public static function factoryMethod(): self
    {
        return new self();
    }
}
