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
 * Proxies call to another reader and caches callees for a length of an entire suite.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ContextReaderCachedPerSuite implements ContextReader
{
    /**
     * @var ContextReader
     */
    private $childReader;
    /**
     * @var array[]
     */
    private $cachedCallees = array();

    /**
     * Initializes reader.
     *
     * @param ContextReader $childReader
     */
    public function __construct(ContextReader $childReader)
    {
        $this->childReader = $childReader;
    }

    /**
     * {@inheritdoc}
     */
    public function readContextCallees(ContextEnvironment $environment, $contextClass)
    {
        $key = $this->generateCacheKey($environment, $contextClass);

        if (isset($this->cachedCallees[$key])) {
            return $this->cachedCallees[$key];
        }

        return $this->cachedCallees[$key] = $this->childReader->readContextCallees(
            $environment, $contextClass
        );
    }

    /**
     * Generates cache key.
     *
     * @param ContextEnvironment $environment
     * @param string             $contextClass
     *
     * @return string
     */
    private function generateCacheKey(ContextEnvironment $environment, $contextClass)
    {
        return $environment->getSuite()->getName() . $contextClass;
    }
}
