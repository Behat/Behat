<?php

namespace Everzet\Behat\Output\Formatter;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Colorable Formatter Interface.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ColorableFormatterInterface
{
    /**
     * Allow colors in output. 
     * 
     * @param   boolean $colors allow colors
     */
    public function showColors($colors = true);
}
