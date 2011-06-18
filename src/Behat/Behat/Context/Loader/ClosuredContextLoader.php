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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ClosuredContextLoader implements ContextLoaderInterface
{
    /**
     * Step definitions loader.
     *
     * @var     Behat\Behat\Definition\ClosuredDefinitionLoader
     */
    private $definitionLoader;
    /**
     * Hooks loader.
     *
     * @var     Behat\Behat\Hook\ClosuredHookLoader
     */
    private $hookLoader;

    /**
     * Initializes context loader.
     *
     * @param   Behat\Behat\Definition\Loader\ClosuredDefinitionLoader  $definitionLoader   definitionLoader
     * @param   Behat\Behat\Hook\Loader\ClosuredHookLoader              $hookLoader         hookLoader
     */
    public function __construct(ClosuredDefinitionLoader $definitionLoader, ClosuredHookLoader $hookLoader)
    {
        $this->definitionLoader = $definitionLoader;
        $this->hookLoader       = $hookLoader;
    }

    /**
     * @see     Behat\Behat\Context\Loader\ContextLoaderInterface::supports()
     */
    public function supports(ContextInterface $context)
    {
        return $context instanceof ClosuredContextInterface;
    }

    /**
     * @see     Behat\Behat\Context\Loader\ContextLoaderInterface::load()
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
