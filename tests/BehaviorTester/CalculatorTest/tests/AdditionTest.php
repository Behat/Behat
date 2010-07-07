<?php

require_once __DIR__ . '/BaseCalculatorTest.php';

class AdditionTest extends BaseCalculatorTest
{
    protected function getFeatureName()
    {
        return 'addition.feature';
    }
}
