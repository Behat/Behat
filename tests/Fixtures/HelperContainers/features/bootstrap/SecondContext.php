<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Tests\Fixtures\Assert;

final class SecondContext implements Context
{
    private SharedService $service;

    public function __construct(SharedService $service)
    {
        $this->service = $service;
    }

    #[Then('service should have a state of :number in second context')]
    public function checkState(string $number): void
    {
        Assert::assertSame($number, $this->service->number);
    }
}
