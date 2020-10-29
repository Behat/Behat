<?php

namespace Behat\Behat\Definition\Translator;

interface TranslatorInterface extends \Symfony\Contracts\Translation\TranslatorInterface
{
    public function getLocale();
}
