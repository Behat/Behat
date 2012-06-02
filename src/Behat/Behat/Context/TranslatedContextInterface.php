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
interface TranslatedContextInterface extends ContextInterface
{
    /**
     * Returns array of i18n XLIFF files paths.
     *
     * @return array
     */
    public function getTranslationResources();
}
