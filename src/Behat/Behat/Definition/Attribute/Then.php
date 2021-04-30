<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Attribute;

/**
 * Represents an Attribute for Then steps
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class Then implements Definition
{
    /**
     * @var string
     */
    public $pattern;

    public function __construct($pattern = null)
    {
        $this->pattern = $pattern;
    }
}
