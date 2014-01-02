<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output;

use Behat\Testwork\Output\Formatter;

/**
 * Behat JUnit formatter.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class JUnitFormatter implements Formatter
{
    /**
     * @var array
     */
    private $parameters = array();
    /**
     * @var string
     */
    private $basePath;

    /**
     * @param string $basePath
     */
    public function __construct($basePath)
    {
        if (null !== $basePath) {
            $realBasePath = realpath($basePath);

            if ($realBasePath) {
                $basePath = $realBasePath;
            }
        }

        $this->basePath = $basePath;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'junit';
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return 'Creates a junit xml file';
    }

    /**
     * {@inheritDoc}
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getParameter($name)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }
}
