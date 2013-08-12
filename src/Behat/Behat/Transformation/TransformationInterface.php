<?php

namespace Behat\Behat\Transformation;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Callee\CalleeInterface;

/**
 * Step transformation interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface TransformationInterface extends CalleeInterface
{
    /**
     * Returns transformation regex.
     *
     * @return string
     */
    public function getRegex();
}
