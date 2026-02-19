<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Exception;

use Behat\Testwork\Deprecation\DeprecationCollector;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * Represents exception caused by a wrong paths argument.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class WrongPathsException extends RuntimeException implements TesterException
{
    /**
     * @var list<string>
     */
    private readonly array $paths;

    /**
     * Initializes exception.
     *
     * @param string $message
     * @param string|list<string> $paths
     */
    public function __construct(
        $message,
        string|array $paths,
    ) {
        parent::__construct($message);
        $this->paths = (array) $paths;
    }

    /**
     * Returns path that caused exception.
     *
     * @return list<string>
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * Returns path that caused exception.
     *
     * @deprecated Use getPaths() instead. Will be removed in 4.0.
     */
    public function getPath(): string
    {
        DeprecationCollector::trigger('WrongPathsException::getPath() is deprecated, use getPaths() instead. It will be removed in 4.0.');

        return implode(', ', $this->paths);
    }
}
