<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Exception;

use Behat\Testwork\Output\Exception\OutputException;
use InvalidArgumentException;

/**
 * Represents an exception caused by a request for non-existent node visitor.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class NodeVisitorNotFoundException extends InvalidArgumentException implements OutputException
{
}
