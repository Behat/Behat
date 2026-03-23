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
use Behat\Testwork\Deprecation\DeprecationCollector;
use Stringable;

/**
 * Transformation that is created and executed in the runtime.
 *
 * @deprecated Will be removed in 4.0. Use specific transformations instead
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @phpstan-import-type TBehatCallable from RuntimeCallee
 */
final class RuntimeTransformation extends RuntimeCallee implements Stringable, Transformation
{
    /**
     * Initializes transformation.
     *
     * @param string      $pattern
     *
     * @phpstan-param TBehatCallable $callable
     */
    public function __construct(
        private $pattern,
        callable|array $callable,
        ?string $description = null,
    ) {
        DeprecationCollector::trigger('RuntimeTransformation is deprecated and will be removed in 4.0. Use specific transformations instead.');

        parent::__construct($callable, $description);
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function __toString(): string
    {
        return 'Transform ' . $this->getPattern();
    }
}
