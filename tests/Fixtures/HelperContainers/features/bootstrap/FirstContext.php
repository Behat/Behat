<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\When;
use Behat\Tests\Fixtures\Assert;

final class FirstContext implements Context
{
    private SharedService $service;

    public function __construct(SharedService $service)
    {
        $this->service = $service;
    }

    #[Given('service has no state')]
    public function noState(): void
    {
        Assert::assertNull($this->service->number);
    }

    #[When('service gets a state of :number in first context')]
    public function setState(string $number): void
    {
        $this->service->number = $number;
    }
}
