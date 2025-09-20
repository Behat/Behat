<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Reader;

use Behat\Behat\Context\Environment\ContextEnvironment;

/**
 * Proxies call to another reader and caches context callees for a length of an entire exercise.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ContextReaderCachedPerContext implements ContextReader
{
    /**
     * @var array[]
     */
    private $cachedCallees = [];

    /**
     * Initializes reader.
     */
    public function __construct(
        private readonly ContextReader $childReader,
    ) {
    }

    public function readContextCallees(ContextEnvironment $environment, $contextClass)
    {
        return $this->cachedCallees[$contextClass] ?? $this->cachedCallees[$contextClass] = $this->childReader->readContextCallees(
            $environment,
            $contextClass
        );
    }
}
