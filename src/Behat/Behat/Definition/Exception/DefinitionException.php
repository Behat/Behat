<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Exception;

use Behat\Testwork\Exception\TestworkException;

/**
 * Represents an exception thrown during step definition handling.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface DefinitionException extends TestworkException
{
}
