<?php

namespace Everzet\Gherkin;

use Symfony\Component\Translation\Translator as BaseTranslator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\LoaderInterface;

/*
 * This file is part of the Gherkin.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Gherkin Translator.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Translator extends BaseTranslator
{
    /**
     * Initializes Translator. 
     * 
     * @param   string          $locale         default locale
     * @param   MessageSelector $selector       choices selector
     * @param   LoaderInterface $xliffLoader    xliff files loader
     * @param   array           $resources      xliff resources
     */
    public function __construct($locale, MessageSelector $selector, LoaderInterface $xliffLoader, $resources)
    {
        parent::__construct($locale, $selector);

        $this->addLoader('xliff', $xliffLoader);

        foreach ($resources as $locale => $resource) {
            $this->addResource('xliff', __DIR__ . '/' . $resource, $locale);
        }
    }

    /**
     * Return current locale. 
     * 
     * @return  string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}

