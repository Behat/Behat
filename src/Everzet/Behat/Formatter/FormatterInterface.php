<?php

namespace Everzet\Behat\Formatter;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base interface for Behat output formatters.
 *
 * @package     Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface FormatterInterface
{
    /**
     * Constructs formatter
     *
     * @param   Container   $container  dependency container
     */
    public function __construct(Container $container);

    /**
     * Registers listeners on formatter
     *
     * @param   EventDispatcher $dispatcher event dispatcher
     */
    public function registerListeners(EventDispatcher $dispatcher);
}
