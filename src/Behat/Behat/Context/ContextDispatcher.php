<?php

namespace Behat\Behat\Context;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Context dispatcher.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextDispatcher
{
    private $classGuessers = array();
    private $initializers  = array();
    private $parameters    = array();

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
     * @param ClassGuesser\ClassGuesserInterface $guesser
     */
    public function addClassGuesser(ClassGuesser\ClassGuesserInterface $guesser)
    {
        $this->classGuessers[] = $guesser;
    }

    /**
     * Adds context initializer to the dispatcher.
     *
     * @param Initializer\InitializerInterface $initializer
     */
    public function addInitializer(Initializer\InitializerInterface $initializer)
    {
        $this->initializers[] = $initializer;
    }

    /**
     * Returns context classname.
     *
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
                'Maybe you have provided a wrong or no `bootstrap` path in your behat.yml:'."\n".
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
                'Context class "%s" should implement ContextInterface', $classname
            ));
        }

        return $classname;
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
     * Initializes context with registered initializers.
     *
     * @param ContextInterface $context
     */
    private function initializeContext(ContextInterface $context)
    {
        foreach ($this->initializers as $initializer) {
            if ($initializer->supports($context)) {
                $initializer->initialize($context);
            }
        }

        // if context has subcontexts - initialize them too
        if ($context instanceof SubcontextableContextInterface) {
            foreach ($context->getSubcontexts() as $subcontext) {
                $this->initializeContext($subcontext);
            }
        }
    }
}
