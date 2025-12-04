<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Step\When;
use InvalidArgumentException;

class FeatureContextMultipleExamples implements Context
{
    private string $output;

    #[When('I input :name')]
    public function input(string $name): void
    {
        $this->output = ctype_digit($name)
            ? "'$name' doesn't look like a name?"
            : 'Hi Bob';
    }

    #[Then('I should see :result')]
    public function assertSee(string $result): void
    {
        if ($this->output !== $result) {
            throw new InvalidArgumentException('Failed - got: ' . $this->output);
        }
    }
}
