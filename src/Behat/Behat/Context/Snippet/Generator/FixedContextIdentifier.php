<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Snippet\Generator;

use Behat\Behat\Context\Environment\ContextEnvironment;

/**
 * Identifier that always returns same context, if it is defined in the suite.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class FixedContextIdentifier implements TargetContextIdentifier
{
    public function __construct(
        private readonly ?string $contextClass = null
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function guessTargetContextClass(ContextEnvironment $environment): ?string
    {
        if ($environment->hasContextClass($this->contextClass)) {
            return $this->contextClass;
        }

        return null;
    }
}
