<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;

class FeatureContext implements Context
{
    #[Given('/^Some slow step N(\d+)$/')]
    public function someSlowStepN($num)
    {
    }

    #[Given('/^Some normal step N(\d+)$/')]
    public function someNormalStepN($num)
    {
    }

    #[Given('/^Some fast step N(\d+)$/')]
    public function someFastStepN($num)
    {
    }
}
