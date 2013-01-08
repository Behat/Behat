<?php

namespace Behat\Behat\Context\Dispatcher;

use Behat\Behat\Context\Initializer\InitializerInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Interface for context dispatchers (creating new context objects after each
 * scenario).
 */
interface DispatcherInterface
{
    /**
     * Adds context initializer to the dispatcher.
     *
     * @param InitializerInterface $initializer
     */
    public function addInitializer(InitializerInterface $initializer);

    /**
     * Returns context classname.
     *
     * @throws \RuntimeException If no class can be found or class can not be created
     * @return string
     */
    public function getContextClass();

    /**
     * Creates new context instance.
     *
     * @return \Behat\Behat\Context\ContextInterface
     *
     * @throws \RuntimeException
     */
    public function createContext();
}
