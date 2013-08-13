<?php

namespace Behat\Behat\Context\Reader\Loader;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Callee\CalleeInterface;
use Behat\Behat\Suite\SuiteInterface;

/**
 * Context loader interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface LoaderInterface
{
    /**
     * Loads callees from specific suite & context.
     *
     * @param SuiteInterface $suite
     * @param string         $contextClass
     *
     * @return CalleeInterface[]
     */
    public function loadCallees(SuiteInterface $suite, $contextClass);
}
