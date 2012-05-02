<?php

namespace Behat\Behat\Definition\Annotation;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Given type step definition.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Given extends Definition
{
    /**
     * Returns definition type (Given|When|Then).
     *
     * @return string
     */
    public function getType()
    {
        return 'Given';
    }
}
