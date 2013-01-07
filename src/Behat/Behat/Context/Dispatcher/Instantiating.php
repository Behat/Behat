<?php

namespace Behat\Behat\Context\Dispatcher;

use Behat\Behat\Context\ClassGuesser\ClassGuesserInterface,
    Behat\Behat\Context\ContextInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Context dispatcher directly instantiating new contexts.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Instantiating extends AbstractDispatcher
{
    private $parameters    = array();
    private $classGuessers = array();

    /**
     * Initialize dispatcher.
     *
     * @param array $parameters context parameters
     */
    public function __construct(array $parameters = array())
    {
        $this->parameters = $parameters;
    }

    /**
     * Adds context class guesser to the dispatcher.
     *
     * @param ClassGuesserInterface $guesser
     */
    public function addClassGuesser(ClassGuesserInterface $guesser)
    {
        $this->classGuessers[] = $guesser;
    }

    /**
     * Returns context parameters.
     *
     * @return array
     */
    public function getContextParameters()
    {
        return $this->parameters;
    }

    /**
     * Creates new context instance.
     *
     * @return ContextInterface
     *
     * @throws \RuntimeException
     */
    public function createContext()
    {
        $classname  = $this->getContextClass();
        $parameters = $this->getContextParameters();
        $context    = new $classname($parameters);

        $this->initializeContext($context);

        return $context;
    }

    /**
     * Returns context classname.
     *
     * @throws \RuntimeException If no class can be found or class can not be created
     * @return string
     */
    public function getContextClass()
    {
        $classname = null;
        foreach ($this->classGuessers as $guesser) {
            if ($classname = $guesser->guess()) {
                break;
            }
        }

        if (null === $classname) {
            throw new \RuntimeException(
                'Context class not found.'."\n".
                    'Maybe you have provided wrong or no `bootstrap` path in your behat.yml:'."\n".
                    'http://docs.behat.org/guides/7.config.html#paths'
            );
        }

        if (!class_exists($classname)) {
            throw new \RuntimeException(sprintf(
                'Context class "%s" not found and can not be instantiated.', $classname
            ));
        }

        $contextClassRefl = new \ReflectionClass($classname);
        if (!$contextClassRefl->implementsInterface('Behat\Behat\Context\ContextInterface')) {
            throw new \RuntimeException(sprintf(
                'Context class "%s" must implement ContextInterface', $classname
            ));
        }

        return $classname;
    }
}
