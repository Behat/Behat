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
use Behat\Testwork\Specification\GroupedSpecificationIterator as Iterator;

/**
 * Encapsulates a suite context.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SuiteContext implements TestContext
{
    /**
     * @var Iterator
     */
    private $iterator;
    /**
     * @var Environment
     */
    private $environment;

    /**
     * Initializes context.
     *
     * @param Iterator    $iterator
     * @param Environment $environment
     */
    public function __construct(Iterator $iterator, Environment $environment)
    {
        $this->iterator = $iterator;
        $this->environment = $environment;
    }

    /**
     * Creates new suite context using provided environment manager.
     *
     * @param Iterator           $iterator
     * @param EnvironmentManager $manager
     *
     * @return SuiteContext
     */
    public static function createUsingManager(Iterator $iterator, EnvironmentManager $manager)
    {
        $environment = $manager->buildEnvironment($iterator->getSuite());

        return new SuiteContext($iterator, $environment);
    }

    /**
     * Returns specification iterator.
     *
     * @return Iterator
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
