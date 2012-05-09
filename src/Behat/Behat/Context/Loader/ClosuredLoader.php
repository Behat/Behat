<?php

namespace Behat\Behat\Context\Loader;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Definition\Loader\ClosuredDefinitionLoader,
    Behat\Behat\Hook\Loader\ClosuredHookLoader;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Closured contexts reader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ClosuredLoader implements LoaderInterface
{
    private $definitionLoader;
    private $hookLoader;

    /**
     * Initializes context loader.
     *
     * @param ClosuredDefinitionLoader $definitionLoader
     * @param ClosuredHookLoader       $hookLoader
     */
    public function __construct(ClosuredDefinitionLoader $definitionLoader, ClosuredHookLoader $hookLoader)
    {
        $this->definitionLoader = $definitionLoader;
        $this->hookLoader       = $hookLoader;
    }

    /**
     * Checks if loader supports provided context.
     *
     * @param ContextInterface $context
     *
     * @return Boolean
     */
    public function supports(ContextInterface $context)
    {
        return $context instanceof ClosuredContextInterface;
    }

    /**
     * Loads definitions and translations from provided context.
     *
     * @param ContextInterface $context
     */
    public function load(ContextInterface $context)
    {
        foreach ($context->getStepDefinitionResources() as $path) {
            $this->definitionLoader->load($path);
        }

        foreach ($context->getHookDefinitionResources() as $path) {
            $this->hookLoader->load($path);
        }
    }
}
