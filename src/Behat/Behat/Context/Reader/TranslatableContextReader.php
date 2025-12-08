<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Reader;

use Behat\Behat\Context\Environment\ContextEnvironment;
use Behat\Behat\Context\Exception\UnknownTranslationResourceException;
use Behat\Behat\Context\TranslatableContext;
use ReflectionClass;
use Symfony\Component\Translation\Translator;

/**
 * Reads translation resources from translatable contexts.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TranslatableContextReader implements ContextReader
{
    /**
     * Initializes loader.
     */
    public function __construct(
        private readonly Translator $translator,
    ) {
    }

    /**
     * @see TranslatableContext
     */
    public function readContextCallees(ContextEnvironment $environment, $contextClass): array
    {
        $reflClass = new ReflectionClass($contextClass);

        if (!$reflClass->implementsInterface(TranslatableContext::class)) {
            return [];
        }

        $assetsId = $environment->getSuite()->getName();
        foreach (call_user_func([$contextClass, 'getTranslationResources']) as $path) {
            $this->addTranslationResource($path, $assetsId);
        }

        return [];
    }

    /**
     * Adds translation resource.
     *
     * @param string $path
     * @param string $assetsId
     *
     * @throws UnknownTranslationResourceException
     */
    private function addTranslationResource($path, $assetsId): void
    {
        match ($ext = pathinfo($path, PATHINFO_EXTENSION)) {
            'yml' => $this->addTranslatorResource('yaml', $path, basename($path, '.' . $ext), $assetsId),
            'xliff' => $this->addTranslatorResource('xliff', $path, basename($path, '.' . $ext), $assetsId),
            'php' => $this->addTranslatorResource('php', $path, basename($path, '.' . $ext), $assetsId),
            default => throw new UnknownTranslationResourceException(sprintf(
                'Can not read translations from `%s`. File type is not supported.',
                $path
            ), $path),
        };
    }

    /**
     * Adds resource to translator instance.
     *
     * @param string $type
     * @param string $path
     * @param string $language
     * @param string $assetsId
     */
    private function addTranslatorResource($type, $path, $language, $assetsId): void
    {
        $this->translator->addResource($type, $path, $language, $assetsId);
    }
}
