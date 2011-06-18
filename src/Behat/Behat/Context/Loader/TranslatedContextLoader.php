<?php

namespace Behat\Behat\Context\Loader;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Context\TranslatedContextInterface;

use Symfony\Component\Translation\TranslatorInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Translated contexts reader.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TranslatedContextLoader implements ContextLoaderInterface
{
    /**
     * Translator.
     *
     * @var     Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * Initializes context loader.
     *
     * @param   Symfony\Component\Translation\TranslatorInterface   $translator             translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @see     Behat\Behat\Context\Loader\ContextLoaderInterface::supports()
     */
    public function supports(ContextInterface $context)
    {
        return $context instanceof TranslatedContextInterface;
    }

    /**
     * @see     Behat\Behat\Context\Loader\ContextLoaderInterface::load()
     */
    public function load(ContextInterface $context)
    {
        foreach ($context->getTranslationResources() as $path) {
            $this->translator->addResource('xliff', $path, basename($path, '.xliff'), 'behat.definitions');
        }
    }
}
