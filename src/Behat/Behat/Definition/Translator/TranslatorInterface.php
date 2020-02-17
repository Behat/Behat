<?php

namespace Behat\Behat\Definition\Translator;

if (interface_exists('Symfony\Contracts\Translation\TranslatorInterface')) {
    interface TranslatorInterface extends \Symfony\Contracts\Translation\TranslatorInterface
    {
    }
} elseif (interface_exists('Symfony\Component\Translation\TranslatorInterface')) {
    interface TranslatorInterface extends \Symfony\Component\Translation\TranslatorInterface
    {
    }
}
