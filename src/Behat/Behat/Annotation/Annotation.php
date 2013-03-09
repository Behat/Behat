<?php

namespace Behat\Behat\Annotation;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Context\SubcontextableContextInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat annotation class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Annotation implements AnnotationInterface
{
    private $closure;
    private $callback;
    private $path;
    private $description;

    /**
     * Constructs annotation.
     *
     * @param callback $callback
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException(sprintf(
                'Annotation callback should be a valid callable, but %s given',
                gettype($callback)
            ));
        }
        $this->closure = !is_array($callback);

        if (!$this->isClosure()) {
            $this->path = $callback[0] . '::' . $callback[1] . '()';
        } else {
            $reflection = new \ReflectionFunction($callback);
            $this->path = $reflection->getFileName() . ':' . $reflection->getStartLine();
        }

        $this->callback = $callback;
    }

    /**
     * Sets annotation description.
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Returns description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns path string for callback.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Checks whether callback is closure.
     *
     * @return Boolean
     */
    public function isClosure()
    {
        return $this->closure;
    }

    /**
     * Returns callback.
     *
     * @return callback
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Returns callback, mapped to specific context (or it's subcontext) instance.
     *
     * @param ContextInterface $context
     *
     * @return callback
     *
     * @throws \RuntimeException
     */
    public function getCallbackForContext(ContextInterface $context)
    {
        $callback = $this->getCallback();

        if (!$this->isClosure()) {
            if ($callback[0] === get_class($context)) {
                $callback = array($context, $callback[1]);
            } elseif ($context instanceof SubcontextableContextInterface) {
                $subcontext = $context->getSubcontextByClassName($callback[0]);

                if (null === $subcontext) {
                    throw new \RuntimeException(sprintf(
                        '"%s" subcontext instance not found in context "%s".'."\n".
                        'Looks like something is wrong with getSubcontextByClassName() method in one of your contexts',
                        $callback[0], get_class($context)
                    ));
                }

                $reflection = new \ReflectionClass($subcontext);
                if (!$reflection->hasMethod($callback[1])) {
                    throw new \RuntimeException(sprintf(
                        'Subcontext "%s" does not have "%s()" method.'."\n".
                        'Looks like something is wrong with getSubcontextByClassName() method in one of your contexts',
                        get_class($subcontext), $callback[1]
                    ));
                }

                $callback = array($subcontext, $callback[1]);
            }
        }

        return $callback;
    }

    /**
     * Returns callback reflection.
     *
     * @return \ReflectionFunction
     */
    public function getCallbackReflection()
    {
        if (!$this->isClosure()) {
            return new \ReflectionMethod($this->callback[0], $this->callback[1]);
        } else {
            return new \ReflectionFunction($this->callback);
        }
    }

    /**
     * Make sure that stuff can be serialized properly.
     *
     * @return array of serializable property names.
     */
    public function __sleep() {
        $serializable = array();
        foreach ($this as $paramName => $paramValue) {
            if (!is_string($paramValue) && !is_array($paramValue) && is_callable($paramValue)) {
                continue;
            }
            $serializable[] = $paramName;
        }
        return $serializable;
    }
}
