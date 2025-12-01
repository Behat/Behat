<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;

final class MyContainer implements ContainerInterface
{
    private ?SharedService $service = null;

    public function has(string $id): bool
    {
        return $id === 'shared_service';
    }

    public function get(string $id): SharedService
    {
        if ($id !== 'shared_service') {
            throw new InvalidArgumentException();
        }

        return $this->service ?? $this->service = new SharedService();
    }
}
