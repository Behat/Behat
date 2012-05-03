<?php

namespace Behat\Behat\Context;

use Behat\Behat\Context\Loader\ContextLoaderInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Context reader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextReader
{
    private $dispatcher;
    private $loaders = array();

    /**
     * Initializes context reader.
     *
     * @param ContextDispatcher $dispatcher
     */
    public function __construct(ContextDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Adds context loader to the list of available loaders.
     *
     * @param ContextLoaderInterface $loader
     */
    public function addLoader(ContextLoaderInterface $loader)
    {
        $this->loaders[] = $loader;
    }

    /**
     * Reads all definition data from main context.
     */
    public function read()
    {
        $this->readFromContext($this->dispatcher->createContext());
    }

    /**
     * Reads definition data from specific context class.
     *
     * @param ContextInterface $context
     */
    private function readFromContext(ContextInterface $context)
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($context)) {
                $loader->load($context);
            }
        }

        if ($context instanceof SubcontextableContextInterface) {
            foreach ($context->getSubcontexts() as $subcontext) {
                $this->readFromContext($subcontext);
            }
        }
    }
}
