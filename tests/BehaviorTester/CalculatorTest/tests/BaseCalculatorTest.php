<?php

abstract class BaseCalculatorTest extends \BehaviorTester\FeatureTestCase
{
    public $numbers = array();
    public $result;

    public function getFeaturesPath()
    {
        return __DIR__ . '/../features/';
    }

    protected function initStepDefinition()
    {
        $iterator = new \RecursiveDirectoryIterator(
            __DIR__ . '/../steps/',
            \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
        );
        $iterator = new \RecursiveIteratorIterator(
            $iterator, \RecursiveIteratorIterator::SELF_FIRST
        );

        $this->steps = array();
        $t = $this;
        foreach ($iterator as $file) {
            require $file;
        }
    }
}
