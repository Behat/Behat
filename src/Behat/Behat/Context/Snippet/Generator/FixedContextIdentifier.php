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
    /**
     * @var
     */
    private $contextClass;

    /**
     * Initialises identifier.
     *
     * @param string $contextClass
     */
    public function __construct($contextClass)
    {
        $this->contextClass = $contextClass;
    }

    /**
     * {@inheritdoc}
     */
    public function guessTargetContextClass(ContextEnvironment $environment)
    {
        if ($environment->hasContextClass($this->contextClass)) {
            return $this->contextClass;
        }

        return null;
    }
}
