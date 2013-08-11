<?php

namespace Behat\Behat\Context\Pool;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\ContextInterface;

/**
 * Context pool interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ContextPoolInterface
{
    /**
     * Checks if pool has any contexts registered.
     *
     * @return Boolean
     */
    public function hasContexts();

    /**
     * Returns list of registered context classes or instances.
     *
     * @return string[]|ContextInterface[]
     */
    public function getContexts();

    /**
     * Returns list of registered context classes.
     *
     * @return string[]
     */
    public function getContextClasses();

    /**
     * Checks if pool contains context with the specified class name.
     *
     * @param string $class
     *
     * @return Boolean
     */
    public function hasContext($class);

    /**
     * Returns registered context class or instance by its class name.
     *
     * @param string $class
     *
     * @return string|ContextInterface
     */
    public function getContext($class);

    /**
     * Returns first registered context class or instance.
     *
     * @return null|string|ContextInterface
     */
    public function getFirstContext();
}
