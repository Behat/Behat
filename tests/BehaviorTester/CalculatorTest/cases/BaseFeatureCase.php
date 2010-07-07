<?php

class BaseFeatureCase extends \BehaviorTester\FeatureTestCase
{
    public $numbers = array();
    public $result;

    protected function getStepsPath()
    {
        return __DIR__ . '/../steps/';
    }
}
