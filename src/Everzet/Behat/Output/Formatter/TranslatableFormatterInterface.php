<?php

namespace Everzet\Behat\Output\Formatter;

use Symfony\Component\Translation\TranslatorInterface;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Translatable Formatter Interface.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface TranslatableFormatterInterface
{
    /**
     * Set Translator Service. 
     * 
     * @param   TranslatorInterface $translator translator service
     */
    public function setTranslator(TranslatorInterface $translator);
}

