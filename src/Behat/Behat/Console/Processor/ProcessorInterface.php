<?php

namespace Behat\Behat\Console\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

interface ProcessorInterface
{
    function getInputOptions();
    function process(ContainerInterface $container, InputInterface $input, OutputInterface $output);
}
