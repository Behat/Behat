<?php

require_once __DIR__ . '/BaseFeatureTest.php';

class AdditionTest extends BaseFeatureTest
{
    public function getFeaturePath()
    {
        return __DIR__ . '/../features/addition.feature';
    }
}
