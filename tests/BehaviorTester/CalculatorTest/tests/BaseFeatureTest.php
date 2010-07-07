<?php

abstract class BaseFeatureTest extends \BehaviorTester\FeatureTestCase
{
    public $numbers = array();
    public $result;

    public function getFeaturesPath()
    {
        return __DIR__ . '/../features/';
    }

    protected function getStepsPath()
    {
        return __DIR__ . '/../steps/';
    }
}
