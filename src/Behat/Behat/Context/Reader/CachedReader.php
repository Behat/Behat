<?php

namespace Behat\Behat\Context\Reader;

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
 * Cached callees reader.
 * Caches callees for specific suite avoiding unnecessary I/O.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class CachedReader extends CalleesReader
{
    /**
     * @var CalleeInterface[string]
     */
    private $callees = array();

    /**
     * Reads callees from specific suite's context.
     *
     * @param SuiteInterface $suite
     * @param string         $contextClass
     *
     * @return CalleeInterface[]
     */
    protected function readContext(SuiteInterface $suite, $contextClass)
    {
        if (isset($this->callees[$contextClass])) {
            return $this->callees[$contextClass];
        }

        return $this->callees[$contextClass] = parent::readContext($suite, $contextClass);
    }
}
