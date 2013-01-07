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
    private $initializers  = array();

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
