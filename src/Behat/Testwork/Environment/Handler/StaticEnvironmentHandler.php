<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Environment\Handler;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\StaticEnvironment;
use Behat\Testwork\Suite\Suite;

/**
 * Represents environment handler based on static calls (without any isolation).
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class StaticEnvironmentHandler implements EnvironmentHandler
{
    /**
     * {@inheritdoc}
     */
    public function supportsSuite(Suite $suite)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function buildEnvironment(Suite $suite)
    {
        return new StaticEnvironment($suite);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEnvironmentAndSubject(Environment $environment, $testSubject = null)
    {
        return $environment instanceof StaticEnvironment;
    }

    /**
     * {@inheritdoc}
     */
    public function isolateEnvironment(Environment $environment, $testSubject = null)
    {
        return $environment;
    }
}
