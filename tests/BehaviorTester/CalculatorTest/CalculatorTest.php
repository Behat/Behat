<?php

class CalculatorTest extends \BehaviorTester\TestCase
{
    public $numbers = array();
    public $result;

    protected function getFeaturesPath()
    {
        return __DIR__ . '/features/';
    }

    protected function getStepsPath()
    {
        return __DIR__ . '/steps/';
    }
}
