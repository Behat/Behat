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
use Stringable;

/**
 * Transformation that is created and executed in the runtime.
 *
 * @deprecated Will be removed in 4.0. Use specific transformations instead
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RuntimeTransformation extends RuntimeCallee implements Stringable, Transformation
{
    /**
     * Initializes transformation.
     *
     * @param string      $pattern
     * @param callable    $callable
     */
    public function __construct(
        private $pattern,
        callable|array $callable,
        ?string $description = null,
    ) {
        parent::__construct($callable, $description);
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function __toString()
    {
        return 'Transform ' . $this->getPattern();
    }
}
