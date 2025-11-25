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

/**
 * Translates definitions using translator component.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class DefinitionTranslator
{
    /**
     * @var array<string, string>
     */
    private array $translationCache = [];

    /**
     * Initialises definition translator.
     */
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * Attempts to translate definition using translator and produce translated one on success.
     *
     * @param string|null $language
     *
     * @return Definition|TranslatedDefinition
     */
    public function translateDefinition(Suite $suite, Definition $definition, $language = null)
    {
        $assetsId = $suite->getName();
        $pattern = $definition->getPattern();
        $cacheKey = sprintf('%s|%s|%s', $assetsId, $pattern, $language ?? $this->getLocale());

        if (isset($this->translationCache[$cacheKey])) {
            $translatedPattern = $this->translationCache[$cacheKey];
        } else {
            $translatedPattern = $this->translator->trans($pattern, [], $assetsId, $language);
            $this->translationCache[$cacheKey] = $translatedPattern;
        }

        if ($pattern != $translatedPattern) {
            return new TranslatedDefinition($definition, $translatedPattern, $language);
        }

        return $definition;
    }

    public function translateInfoText(string $infoText, array $parameters): string
    {
        return $this->translator->trans($infoText, $parameters, 'output');
    }

    public function getLocale()
    {
        return $this->translator->getLocale();
    }
}
