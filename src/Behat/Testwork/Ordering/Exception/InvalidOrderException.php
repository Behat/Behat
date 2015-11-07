<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Ordering\Exception;

use Behat\Testwork\Exception\TestworkException;
use RuntimeException;

/**
 * Represents exception throw during attempt to prioritise execution with a non-existent algorithm
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
final class InvalidOrderException extends RuntimeException implements TestworkException
{}
