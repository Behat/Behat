<?php

namespace Behat\Behat\Context\Loader;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Definition\Loader\ClosuredDefinitionLoader,
    Behat\Behat\Hook\Loader\ClosuredHookLoader;

use Symfony\Component\Translation\TranslatorInterface;

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
    private $hookDispatcher;
    /**
     * Translator.
     *
     * @var     Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * Initializes context loader.
     *
     * @param   Behat\Behat\Definition\Loader\ClosuredDefinitionLoader  $definitionLoader   definitionLoader
     * @param   Behat\Behat\Hook\Loader\ClosuredHookLoader              $hookLoader         hookLoader
     * @param   Symfony\Component\Translation\TranslatorInterface       $translator         translator
     */
    public function __construct(ClosuredDefinitionLoader $definitionLoader, ClosuredHookLoader $hookLoader, 
                                TranslatorInterface $translator)
    {
        $this->definitionLoader = $definitionLoader;
        $this->hookLoader       = $hookLoader;
        $this->translator       = $translator;
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
        $this->readStepDefinitions($context);
        $this->readHookDefinitions($context);
        $this->readTranslations($context);
    }

    /**
     * Reads step definitions from context.
     *
     * @param   Behat\Behat\Context\ClosuredContextInterface   $context
     */
    private function readStepDefinitions(ClosuredContextInterface $context)
    {
        foreach ($context->getStepDefinitionResources() as $path) {
            $this->definitionLoader->load($path);
        }
    }

    /**
     * Reads hook definitions from context.
     *
     * @param   Behat\Behat\Context\ClosuredContextInterface   $context
     */
    private function readHookDefinitions(ClosuredContextInterface $context)
    {
        foreach ($context->getHookDefinitionResources() as $path) {
            $this->hookLoader->load($path);
        }
    }

    /**
     * Reads annotated context translations.
     *
     * @param   Behat\Behat\Context\ClosuredContextInterface   $context
     */
    private function readTranslations(ClosuredContextInterface $context)
    {
        foreach ($context->getI18nResources() as $path) {
            $this->translator->addResource('xliff', $path, basename($path, '.xliff'), 'behat.definitions');
        }
    }
}
