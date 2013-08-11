<?php

namespace Behat\Behat\Extension;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use ReflectionObject;
use RuntimeException;
use Symfony\Component\Config\Resource\FileResource;

/**
 * Extensions manager.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ExtensionManager
{
    /**
     * @var string
     */
    private $basePath;
    /**
     * @var ExtensionInterface[string]
     */
    private $extensions = array();

    /**
     * Initializes manager.
     *
     * @param string $basePath base path where to search extension files
     */
    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Activate extension by its id.
     *
     * @param string $id phar file name, php file name, class name
     *
     * @return string
     */
    public function activateExtension($id)
    {
        $extensionId = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $id));
        if (!isset($this->extensions[$extensionId])) {
            $this->extensions[$extensionId] = $this->initializeExtension($id);
        }

        return $extensionId;
    }

    /**
     * Returns specific extension by its id.
     *
     * @param string $id
     *
     * @return ExtensionInterface
     *
     * @throws \RuntimeException
     */
    public function getExtension($id)
    {
        if (!isset($this->extensions[$id])) {
            throw new \RuntimeException(
                sprintf('Extension "%s" has not been activated.', $id)
            );
        }

        return $this->extensions[$id];
    }

    /**
     * Returns all activated extensions.
     *
     * @return ExtensionInterface[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Returns activated extension classes.
     *
     * @return array
     */
    public function getExtensionClasses()
    {
        return array_unique(
            array_map(
                function ($extension) {
                    return get_class($extension);
                },
                $this->extensions
            )
        );
    }

    public function getExtensionResources()
    {
        return array_map(
            function ($extension) {
                $reflection = new ReflectionObject($extension);

                return new FileResource($reflection->getFileName());
            },
            $this->extensions
        );
    }

    /**
     * Initializes extension by id.
     *
     * @param string $id
     *
     * @return ExtensionInterface
     *
     * @throws RuntimeException
     */
    protected function initializeExtension($id)
    {
        $extension = null;
        if (class_exists($id)) {
            $extension = new $id;
        } elseif (file_exists($this->basePath . DIRECTORY_SEPARATOR . $id)) {
            $extension = require($this->basePath . DIRECTORY_SEPARATOR . $id);
        } else {
            $extension = require($id);
        }

        if (null === $extension) {
            throw new RuntimeException(sprintf(
                '"%s" extension could not be found.', $id
            ));
        }
        if (!is_object($extension)) {
            throw new RuntimeException(sprintf(
                '"%s" extension could not be initialized.', $id
            ));
        }
        if (!$extension instanceof ExtensionInterface) {
            throw new RuntimeException(sprintf(
                '"%s" extension class should implement ExtensionInterface.',
                get_class($extension)
            ));
        }

        return $extension;
    }
}
