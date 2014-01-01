<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\ServiceContainer\Exception;

use InvalidArgumentException;

/**
 * Testwork configuration loading exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ConfigurationLoadingException extends InvalidArgumentException implements ServiceContainerException
{
}
