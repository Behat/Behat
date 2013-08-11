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
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Context\Reader\Loader\LoaderInterface;
use Behat\Behat\Suite\SuiteInterface;

/**
 * Callees reader.
 * Reads context pool callees using registered loaders.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class CalleesReader
{
    /**
     * @var LoaderInterface[]
     */
    private $loaders = array();

    /**
     * Registers callee loader.
     *
     * @param LoaderInterface $loader
     */
    public function registerLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
    }

    /**
     * Reads callees from specific suite's context pool.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     *
     * @return CalleeInterface[]
     */
    public function read(SuiteInterface $suite, ContextPoolInterface $contexts)
    {
        $callees = array();
        foreach ($contexts->getContextClasses() as $contextClass) {
            $callees = array_merge($callees, $this->readContext($suite, $contextClass));
        }

        return $callees;
    }

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
        $callees = array();

        foreach ($this->loaders as $loader) {
            $callees = array_merge($callees, $loader->loadCallees($suite, $contextClass));
        }

        return $callees;
    }
}
