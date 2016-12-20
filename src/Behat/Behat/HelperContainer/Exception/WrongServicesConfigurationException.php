<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\HelperContainer\Exception;

use RuntimeException;

/**
 * Represents an exception when wrong value passed into `services` setting.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class WrongServicesConfigurationException extends RuntimeException implements HelperContainerException
{
}
