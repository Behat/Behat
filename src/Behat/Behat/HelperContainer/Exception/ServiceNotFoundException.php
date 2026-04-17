<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\HelperContainer\Exception;

use InvalidArgumentException;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Represents an exception thrown when service ID is not found inside the container.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ServiceNotFoundException extends InvalidArgumentException implements HelperContainerException, NotFoundExceptionInterface
{
    /**
     * Initializes exception.
     */
    public function __construct(
        string $message,
        private readonly string $serviceId,
    ) {
        parent::__construct($message);
    }

    /**
     * Returns service ID that caused exception.
     */
    public function getServiceId(): string
    {
        return $this->serviceId;
    }
}
