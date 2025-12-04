<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Behat\Step\Given;

class FeatureContextBackgroundFailing implements Context
{
    #[Given('/^.*$/')]
    public function anything(?TableNode $table = null): void
    {
        throw new PendingException();
    }
}
