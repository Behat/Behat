<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition;

use Behat\Behat\Definition\Exception\RedundantStepException;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\EnvironmentManager;

/**
 * Provides step definitions using environment manager.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class DefinitionRepository
{
    /**
     * @var EnvironmentManager
     */
    private $environmentManager;

    /**
     * Initializes repository.
     *
     * @param EnvironmentManager $environmentManager
     */
    public function __construct(EnvironmentManager $environmentManager)
    {
        $this->environmentManager = $environmentManager;
    }

    /**
     * Returns all available definitions for a specific environment.
     *
     * @param Environment $environment
     *
     * @return Definition[]
     *
     * @throws RedundantStepException
     */
    public function getEnvironmentDefinitions(Environment $environment)
    {
        $patterns = array();
        $definitions = array();

        foreach ($this->environmentManager->readEnvironmentCallees($environment) as $callee) {
            if (!$callee instanceof Definition) {
                continue;
            }

            $pattern = $callee->getPattern();
            if (isset($patterns[$pattern])) {
                throw new RedundantStepException($callee, $patterns[$pattern]);
            }

            $patterns[$pattern] = $callee;

            $definitions[] = $callee;
        }

        return $definitions;
    }
}
