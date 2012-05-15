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
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TranslatedLoader implements LoaderInterface
{
    private $translator;

    /**
     * Initializes context loader.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
        return $context instanceof TranslatedContextInterface;
    }

    /**
     * Loads definitions and translations from provided context.
     *
     * @param ContextInterface $context
     *
     * @throws \InvalidArgumentException
     */
    public function load(ContextInterface $context)
    {
        foreach ($context->getTranslationResources() as $path) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);

            if ('yml' === $extension) {
                $this->translator->addResource(
                    'yaml', $path, basename($path, '.yml'), 'behat.definitions'
                );
            } elseif ('xliff' === $extension) {
                $this->translator->addResource(
                    'xliff', $path, basename($path, '.xliff'), 'behat.definitions'
                );
            } elseif ('php' === $extension) {
                $this->translator->addResource(
                    'php', $path, basename($path, '.php'), 'behat.definitions'
                );
            } else {
                throw new \InvalidArgumentException(sprintf(
                    'Can not read definitions translations from file "%s". File is not supported',
                    $path
                ));
            }
        }
    }
}
