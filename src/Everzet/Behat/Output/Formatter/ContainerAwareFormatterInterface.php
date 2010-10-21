<?php

namespace Everzet\Behat\Output\Formatter;

use Symfony\Component\DependencyInjection\Container;

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
interface ContainerAwareFormatterInterface
{
    /**
     * Set DI Container Service. 
     * 
     * @param   Container   $container 
     */
    public function setContainer(Container $container);
}

