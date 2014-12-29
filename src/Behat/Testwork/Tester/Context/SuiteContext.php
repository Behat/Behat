<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Context;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Specification\GroupedSpecificationIterator;

/**
 * Encapsulates a suite context.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SuiteContext implements TestContext
{
    /**
     * @var GroupedSpecificationIterator
     */
    private $iterator;
    /**
     * @var Environment
     */
    private $environment;

    /**
     * Initializes context.
     *
     * @param GroupedSpecificationIterator $iterator
     * @param Environment                  $environment
     */
    public function __construct(GroupedSpecificationIterator $iterator, Environment $environment)
    {
        $this->iterator = $iterator;
        $this->environment = $environment;
    }

    /**
     * Creates new suite context using provided environment manager.
     *
     * @param GroupedSpecificationIterator $iterator
     * @param EnvironmentManager           $manager
     *
     * @return SuiteContext
     */
    public static function createUsingManager(GroupedSpecificationIterator $iterator, EnvironmentManager $manager)
    {
        $environment = $manager->buildEnvironment($iterator->getSuite());

        return new SuiteContext($iterator, $environment);
    }

    /**
     * Returns specification iterator.
     *
     * @return GroupedSpecificationIterator
     */
    public function getSpecificationIterator()
    {
        return $this->iterator;
    }

    /**
     * Returns environment.
     *
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
}
