<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\HelperContainer\Exception;

use Behat\Testwork\Environment\Exception\EnvironmentException;
use Interop\Container\Exception\ContainerException;

/**
 * All HelperContainer exceptions implement this interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface HelperContainerException extends ContainerException, EnvironmentException
{
}
