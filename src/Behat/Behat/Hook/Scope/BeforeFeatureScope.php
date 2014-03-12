<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Scope;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Suite\Suite;

/**
 * Represents a BeforeFeature hook scope.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeFeatureScope implements FeatureScope
{
    /**
     * @var Environment
     */
    private $environment;
    /**
     * @var FeatureNode
     */
    private $feature;

    /**
     * Initializes scope.
     *
     * @param Environment $env
     * @param FeatureNode $feature
     */
    public function __construct(Environment $env, FeatureNode $feature)
    {
        $this->environment = $env;
        $this->feature = $feature;
    }

    /**
     * Returns hook scope name.
     *
     * @return string
     */
    public function getName()
    {
        return self::BEFORE;
    }

    /**
     * Returns hook suite.
     *
     * @return Suite
     */
    public function getSuite()
    {
        return $this->environment->getSuite();
    }

    /**
     * Returns hook environment.
     *
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Returns scope feature.
     *
     * @return FeatureNode
     */
    public function getFeature()
    {
        return $this->feature;
    }
}
