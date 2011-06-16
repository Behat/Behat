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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextReader
{
    /**
     * Context class name.
     *
     * @var     string
     */
    private $contextClassName;
    /**
     * Context initialization parameters.
     *
     * @var     array
     */
    private $parameters = array();
    /**
     * Context loaders.
     *
     * @var     array
     */
    private $loaders = array();

    /**
     * Initializes context reader.
     *
     * @param   string  $contextClassName   context class
     * @param   array   $parameters         context params
     */
    public function __construct($contextClassName, array $parameters = array())
    {
        $this->contextClassName = $contextClassName;
        $this->parameters       = $parameters;
    }

    /**
     * Adds context loader to the list of available loaders.
     *
     * @param   Behat\Behat\Context\Loader\ContextLoaderInterface   $loader
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
        $this->readFromContext(new $this->contextClassName($this->parameters));
    }

    /**
     * Reads definition data from specific context class.
     *
     * @param   Behat\Behat\Context\ContextInterface    $context
     */
    private function readFromContext(ContextInterface $context)
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($context)) {
                $loader->load($context);
            }
        }

        foreach ($context->getSubcontexts() as $subcontext) {
            $this->readFromContext($subcontext);
        }
    }
}
