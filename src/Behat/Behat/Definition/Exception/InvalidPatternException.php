<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Exception;

use InvalidArgumentException;

/**
 * Represents an exception caused by an invalid definition pattern (not able to transform it to a regex).
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
final class InvalidPatternException extends InvalidArgumentException implements DefinitionException
{
}
