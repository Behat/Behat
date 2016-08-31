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
 * Decorates actual identifier and caches its answers per suite.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class CachedContextIdentifier implements TargetContextIdentifier
{
    /**
     * @var TargetContextIdentifier
     */
    private $decoratedIdentifier;
    /**
     * @var array
     */
    private $contextClasses = array();

    /**
     * Initialise the identifier.
     *
     * @param TargetContextIdentifier $identifier
     */
    public function __construct(TargetContextIdentifier $identifier)
    {
        $this->decoratedIdentifier = $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function guessTargetContextClass(ContextEnvironment $environment)
    {
        $suiteKey = $environment->getSuite()->getName();

        if (array_key_exists($suiteKey, $this->contextClasses)) {
            return $this->contextClasses[$suiteKey];
        }

        return $this->contextClasses[$suiteKey] = $this->decoratedIdentifier->guessTargetContextClass($environment);
    }
}
