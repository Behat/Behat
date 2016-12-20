<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\HelperContainer;

use Interop\Container\ContainerInterface;

/**
 * Stores services available for contexts and injected via argument resolvers.
 *
 * @see ServicesResolver
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ServiceContainer extends ContainerInterface
{
}
