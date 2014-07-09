<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Translator;

use Behat\Behat\Definition\Definition;
use Behat\Testwork\Suite\Suite;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Translates definitions using translator component.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class DefinitionTranslator
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Initialises definition translator.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Attempts to translate definition using translator and produce translated one on success.
     *
     * @param Suite       $suite
     * @param Definition  $definition
     * @param null|string $language
     *
     * @return Definition|TranslatedDefinition
     */
    public function translateDefinition(Suite $suite, Definition $definition, $language = null)
    {
        $assetsId = $suite->getName();
        $pattern = $definition->getPattern();

        $translatedPattern = $this->translator->trans($pattern, array(), $assetsId, $language);
        if ($pattern != $translatedPattern) {
            return new TranslatedDefinition($definition, $translatedPattern, $language);
        }

        return $definition;
    }
}
