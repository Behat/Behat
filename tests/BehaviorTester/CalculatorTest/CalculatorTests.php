<?php

class CalculatorTests
{
    public static function suite()
    {
        $suite = new \BehaviorTester\FeaturesTestSuite(__DIR__ . '/features');

        return $suite;
    }
}