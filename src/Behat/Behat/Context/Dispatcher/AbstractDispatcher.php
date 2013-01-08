<?php

namespace Behat\Behat\Context\Dispatcher;

use Behat\Behat\Context\ClassGuesser\ClassGuesserInterface,
    Behat\Behat\Context\ContextInterface,
    Behat\Behat\Context\Initializer\InitializerInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
