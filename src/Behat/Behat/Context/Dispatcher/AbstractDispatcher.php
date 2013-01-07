<?php

namespace Behat\Behat\Context\Dispatcher;

use Behat\Behat\Context\ClassGuesser\ClassGuesserInterface,
    Behat\Behat\Context\ContextInterface,
    Behat\Behat\Context\Initializer\InitializerInterface;

/**
 * Dispatcher implementation providing the basic class guesser and initializer
 * logic.
 */
abstract class AbstractDispatcher implements DispatcherInterface
{
    private $classGuessers = array();
    private $initializers  = array();

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
     * Adds context initializer to the dispatcher.
     *
     * @param InitializerInterface $initializer
     */
    public function addInitializer(InitializerInterface $initializer)
    {
        $this->initializers[] = $initializer;
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
                'Context class "%s" should implement ContextInterface', $classname
            ));
        }

        return $classname;
    }

    /**
     * Initializes context with registered initializers.
     *
     * @param ContextInterface $context
     */
    protected function initializeContext(ContextInterface $context)
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
