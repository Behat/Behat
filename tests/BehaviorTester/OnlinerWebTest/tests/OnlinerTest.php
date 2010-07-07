<?php

require_once __DIR__ . '/BaseOnlinerWebTest.php';

class OnlinerTest extends BaseOnlinerWebTest
{
    protected function getFeatureName()
    {
        return 'onliner.feature';
    }
}
