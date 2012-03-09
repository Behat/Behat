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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Annotation implements AnnotationInterface
{
    /**
     * Is callback a closure.
     *
     * @var     Boolean
     */
    private $closure;
    /**
     * Annotation callback.
     *
     * @var     callback
     */
    private $callback;
    /**
     * Definition path.
     *
     * @var     string
     */
    private $path;
    /**
     * Description string (first docBlock string).
     *
     * @var     string
     */
    private $description;

    /**
     * Constructs annotation.
     *
     * @param   callback    $callback
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
     * @param   string  $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Returns description.
     *
     * @return  string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @see Behat\Behat\Annotation\AnnotationInterface::getPath()
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @see Behat\Behat\Annotation\AnnotationInterface::isClosure()
     */
    public function isClosure()
    {
        return $this->closure;
    }

    /**
     * @see Behat\Behat\Annotation\AnnotationInterface::getCallback()
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Returns callback, mapped to specific context (or it's subcontext) instance.
     *
     * @param   Behat\Behat\Context\ContextInterface    $context    context instance
     *
     * @return  mixed                                               context callback
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
     * @see Behat\Behat\Annotation\AnnotationInterface::getCallbackReflection()
     */
    public function getCallbackReflection()
    {
        if (!$this->isClosure()) {
            return new \ReflectionMethod($this->callback[0], $this->callback[1]);
        } else {
            return new \ReflectionFunction($this->callback);
        }
    }
}
