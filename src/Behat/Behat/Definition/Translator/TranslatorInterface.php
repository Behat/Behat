<?php

namespace Behat\Behat\Definition\Translator;

if (interface_exists(\Symfony\Contracts\Translation\TranslatorInterface::class)) {
    interface TranslatorInterface extends \Symfony\Contracts\Translation\TranslatorInterface
    {
    }
} else {
    interface TranslatorInterface extends /** @scrutinizer ignore-type */ \Symfony\Component\Translation\TranslatorInterface
    {
    }
}
