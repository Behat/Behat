<?php

namespace Behat\Behat\Context\Dispatcher;

use Behat\Behat\Context\ClassGuesser\ClassGuesserInterface,
    Behat\Behat\Context\Initializer\InitializerInterface;

/**
 * Interface for context dispatchers (creating new context objects after each
 * scenario).
 */
interface DispatcherInterface
{
    /**
     * Adds context class guesser to the dispatcher.
     *
     * @param ClassGuesserInterface $guesser
     */
    public function addClassGuesser(ClassGuesserInterface $guesser);

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
