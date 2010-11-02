<?php

namespace Everzet\Behat\Output\Formatter;

use Symfony\Component\EventDispatcher\EventDispatcher;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Formatter Interface.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface FormatterInterface
{
    /**
     * Register listeners in formatter.
     *
     * @param   EventDispatcher $dispatcher event dispatcher
     */
    public function registerListeners(EventDispatcher $dispatcher);

    /**
     * Set support directory path (used for templates). 
     * 
     * @param   string  $path   path to support directory
     */
    public function setSupportPath($path);
}
