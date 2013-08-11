<?php

namespace Behat\Behat\Context;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Translated context interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface TranslatableContextInterface extends ContextInterface
{
    /**
     * Returns array of Translator-supported resource paths.
     *
     * For instance:
     *
     *  * array(__DIR__.'/../'ru.yml)
     *  * array(__DIR__.'/../'en.xliff)
     *  * array(__DIR__.'/../'de.php)
     *
     * @return string[]
     */
    public static function getTranslationResources();
}
