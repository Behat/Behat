<?php

namespace Behat\Behat\Context;

use Behat\Behat\Definition\DefinitionDispatcher,
    Behat\Behat\Hook\HookDispatcher;

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
    private $contextDispatcher;
    private $definitionDispatcher;
    private $hookDispatcher;
    private $loaders = array();

    /**
     * Initializes context reader.
     *
     * @param ContextDispatcher    $contextDispatcher
     * @param DefinitionDispatcher $definitionDispatcher
     * @param HookDispatcher       $hookDispatcher
     */
    public function __construct(ContextDispatcher $contextDispatcher,
                                DefinitionDispatcher $definitionDispatcher,
                                HookDispatcher $hookDispatcher)
    {
        $this->contextDispatcher    = $contextDispatcher;
        $this->definitionDispatcher = $definitionDispatcher;
        $this->hookDispatcher       = $hookDispatcher;
    }

    /**
     * Adds context loader to the list of available loaders.
     *
     * @param Loader\ContextLoaderInterface $loader
     */
    public function addLoader(Loader\ContextLoaderInterface $loader)
    {
        $this->loaders[] = $loader;
    }

    /**
     * Reads all definition data from main context.
     */
    public function read()
    {
        // remove old data
        $this->definitionDispatcher->clean();
        $this->hookDispatcher->clean();

        // load new data
        $this->readFromContext($this->contextDispatcher->createContext());
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
