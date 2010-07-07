<?php

abstract class BaseFeatureTest extends \BehaviorTester\WebFeatureTestCase
{
    public function getFeaturesPath()
    {
        return __DIR__ . '/../features/';
    }

    public function pathTo($page)
    {
        switch ($page) {
            case 'главная': return 'http://www.onliner.by/';
            case 'каталог': return 'http://catalog.onliner.by/';
            default: return 'localhost';
        }
    }
}
