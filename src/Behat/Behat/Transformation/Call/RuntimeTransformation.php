<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Call;

use Behat\Behat\Transformation\Transformation;
use Behat\Testwork\Call\RuntimeCallee;

/**
 * Runtime step transformation.
 *
 * Transformation that is created and executed in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RuntimeTransformation extends RuntimeCallee implements Transformation
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * Initializes transformation.
     *
     * @param string      $pattern
     * @param callable    $callable
     * @param null|string $description
     */
    public function __construct($pattern, $callable, $description = null)
    {
        $this->pattern = $pattern;

        parent::__construct($callable, $description);
    }

    /**
     * Returns transformation pattern exactly as it was defined.
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Represents transformation as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return 'Transform ' . $this->getPattern();
    }
}
