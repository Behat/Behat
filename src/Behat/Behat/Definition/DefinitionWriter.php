<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition;

use Behat\Behat\Definition\Printer\DefinitionPrinter;
use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Suite\Suite;

/**
 * Prints definitions using provided printer.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class DefinitionWriter
{
    /**
     * @var EnvironmentManager
     */
    private $environmentManager;
    /**
     * @var DefinitionRepository
     */
    private $repository;

    /**
     * Initializes writer.
     *
     * @param EnvironmentManager   $environmentManager
     * @param DefinitionRepository $repository
     */
    public function __construct(EnvironmentManager $environmentManager, DefinitionRepository $repository)
    {
        $this->environmentManager = $environmentManager;
        $this->repository = $repository;
    }

    /**
     * Prints definitions for provided suite using printer.
     *
     * @param DefinitionPrinter $printer
     * @param Suite             $suite
     */
    public function printSuiteDefinitions(DefinitionPrinter $printer, $suite)
    {
        $environment = $this->environmentManager->buildEnvironment($suite);
        $definitions = $this->repository->getEnvironmentDefinitions($environment);

        $printer->printDefinitions($suite, $definitions);
    }
}
