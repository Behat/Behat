<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Gherkin\Suite\Generator;

use Behat\Testwork\Suite\Generator\SuiteGenerator;
use Behat\Testwork\Suite\GenericSuite;

/**
 * Gherkin suite generator.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class GherkinSuiteGenerator implements SuiteGenerator
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * Initializes generator.
     *
     * @param string $basePath
     */
    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Checks if generator support provided suite type and settings.
     *
     * @param string $type
     * @param array  $settings
     *
     * @return Boolean
     */
    public function supportsTypeAndSettings($type, array $settings)
    {
        return null === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function generateSuite($suiteName, array $settings)
    {
        return new GenericSuite($suiteName, array_merge(
            array(
                'paths'    => array($this->basePath . DIRECTORY_SEPARATOR . 'features'),
                'contexts' => array('FeatureContext' => array())
            ),
            $settings
        ));
    }
}
