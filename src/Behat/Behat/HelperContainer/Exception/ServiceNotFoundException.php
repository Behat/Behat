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

/**
 * Represents an exception thrown when service ID is not found inside the container.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ServiceNotFoundException extends InvalidArgumentException implements HelperContainerException, NotFoundException
{
    /**
     * @var string
     */
    private $serviceId;

    /**
     * Initializes exception.
     *
     * @param string $message
     * @param string $serviceId
     */
    public function __construct($message, $serviceId)
    {
        $this->serviceId = $serviceId;

        parent::__construct($message);
    }

    /**
     * Returns service ID that caused exception.
     *
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }
}
