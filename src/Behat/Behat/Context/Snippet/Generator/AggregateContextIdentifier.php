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
 * Uses multiple child identifiers - the first one that returns non-null result would
 * be the winner.
 *
 * This behaviour was introduced in 3.x to support the BC for interface-focused
 * context identifier, while providing better user experience (no need to explicitly
 * call `--snippets-for` on `--append-snippets` when contexts do not implement any
 * snippet accepting interfaces).
 */
final class AggregateContextIdentifier implements TargetContextIdentifier
{
    /**
     * @var TargetContextIdentifier[]
     */
    private $identifiers;

    /**
     * Initialises identifier.
     *
     * @param TargetContextIdentifier[] $identifiers
     */
    public function __construct(array $identifiers)
    {
        $this->identifiers = $identifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function guessTargetContextClass(ContextEnvironment $environment)
    {
        foreach ($this->identifiers as $identifier) {
            $contextClass = $identifier->guessTargetContextClass($environment);

            if (null !== $contextClass) {
                return $contextClass;
            }
        }

        return null;
    }
}
