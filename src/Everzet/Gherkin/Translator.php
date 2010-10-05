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
     * Return current locale. 
     * 
     * @return  string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}

