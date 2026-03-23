<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Call;

use Behat\Behat\Hook\Scope\FeatureScope;
use Behat\Testwork\Call\RuntimeCallee;

/**
 * Represents a BeforeFeature hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @phpstan-import-type TBehatCallable from RuntimeCallee
 */
final class BeforeFeature extends RuntimeFeatureHook
{
    /**
     * Initializes hook.
     *
     * @param string|null $filterString
     *
     * @phpstan-param TBehatCallable $callable
     */
    public function __construct($filterString, callable|array $callable, ?string $description = null)
    {
        parent::__construct(FeatureScope::BEFORE, $filterString, $callable, $description);
    }

    public function getName(): string
    {
        return 'BeforeFeature';
    }
}
