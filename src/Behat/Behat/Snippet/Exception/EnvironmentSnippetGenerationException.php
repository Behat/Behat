<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Snippet\Exception;

use Behat\Testwork\Environment\Environment;
use RuntimeException;

/**
 * Represents exception caused by an attempt to generate snippet for unsupported environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class EnvironmentSnippetGenerationException extends RuntimeException implements SnippetException
{
    /**
     * @var Environment
     */
    private $environment;

    /**
     * Initializes exception.
     *
     * @param string      $message
     * @param Environment $environment
     */
    public function __construct($message, Environment $environment)
    {
        $this->environment = $environment;

        parent::__construct($message);
    }

    /**
     * Returns environment that caused exception.
     *
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }
}
