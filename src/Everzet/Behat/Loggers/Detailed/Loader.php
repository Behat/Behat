<?php

namespace Everzet\Behat\Loggers\Detailed;

use \Symfony\Components\DependencyInjection\Container;

use \Everzet\Behat\Loggers\Loader as LoaderInterface;

class Loader implements LoaderInterface
{
    public function load(Container $container)
    {
        $output = $container->getParameter('output');
        $output->setStyle('failed',      array('fg' => 'red'));
        $output->setStyle('undefined',   array('fg' => 'yellow'));
        $output->setStyle('pending',     array('fg' => 'yellow'));
        $output->setStyle('passed',      array('fg' => 'green'));
        $output->setStyle('skipped',     array('fg' => 'cyan'));
        $output->setStyle('comment',     array('fg' => 'black'));
        $output->setStyle('tag',         array('fg' => 'cyan'));

        $container->setParameter('logger.scenario.outline.class',
            'Everzet\\Behat\\Loggers\\Detailed\\ScenarioOutlineLogger');
        $container->setParameter('logger.scenario.class',
            'Everzet\\Behat\\Loggers\\Detailed\\ScenarioLogger');
        $container->setParameter('logger.background.class',
            'Everzet\\Behat\\Loggers\\Detailed\\BackgroundLogger');
        $container->setParameter('logger.feature.class',
            'Everzet\\Behat\\Loggers\\Detailed\\FeatureLogger');
        $container->setParameter('logger.step.class',
            'Everzet\\Behat\\Loggers\\Detailed\\StepLogger');
    }
}
