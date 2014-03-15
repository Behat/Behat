<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Exception;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Exception\TestworkException;
use RuntimeException;

/**
 * Represents exception throw during attempt to test non-existent feature background.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class FeatureHasNoBackgroundException extends RuntimeException implements TestworkException
{
    /**
     * @var FeatureNode
     */
    private $feature;

    /**
     * Initializes exception.
     *
     * @param string      $message
     * @param FeatureNode $feature
     */
    public function __construct($message, FeatureNode $feature)
    {
        $this->feature = $feature;

        parent::__construct($message);
    }

    /**
     * Returns feature that caused exception.
     *
     * @return FeatureNode
     */
    public function getFeature()
    {
        return $this->feature;
    }
}
