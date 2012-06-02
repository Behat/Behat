<?php

namespace Behat\Behat\Context;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat basic context implementation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatContext implements ExtendedContextInterface
{
    private $subcontexts = array();
    private $parentContext;

    /**
     * Adds subcontext to current context.
     *
     * @param string                   $alias
     * @param ExtendedContextInterface $context
     */
    public function useContext($alias, ExtendedContextInterface $context)
    {
        $context->setParentContext($this);
        $this->subcontexts[$alias] = $context;
    }

    /**
     * Sets parent context of current context.
     *
     * @param ExtendedContextInterface $parentContext
     */
    public function setParentContext(ExtendedContextInterface $parentContext)
    {
        $this->parentContext = $parentContext;
    }

    /**
     * Returns main context.
     *
     * @return ExtendedContextInterface
     */
    public function getMainContext()
    {
        if (null !== $this->parentContext) {
            return $this->parentContext->getMainContext();
        }

        return $this;
    }

    /**
     * Find current context's subcontext by alias name.
     *
     * @param string $alias
     *
     * @return ExtendedContextInterface
     */
    public function getSubcontext($alias)
    {
        // search in current context subcontexts
        if (isset($this->subcontexts[$alias])) {
            return $this->subcontexts[$alias];
        }

        // search in subcontexts childs contexts
        foreach ($this->subcontexts as $subcontext) {
            if (null !== $context = $subcontext->getSubcontext($alias)) {
                return $context;
            }
        }
    }

    /**
     * Returns all added subcontexts.
     *
     * @return array
     */
    public function getSubcontexts()
    {
        return $this->subcontexts;
    }

    /**
     * Finds subcontext by it's name.
     *
     * @param string $className
     *
     * @return ContextInterface
     */
    public function getSubcontextByClassName($className)
    {
        foreach ($this->getSubcontexts() as $subcontext) {
            if (get_class($subcontext) === $className) {
                return $subcontext;
            }
            if ($context = $subcontext->getSubcontextByClassName($className)) {
                return $context;
            }
        }
    }

    /**
     * Prints beautified debug string.
     *
     * @param string $string debug string
     */
    public function printDebug($string)
    {
        echo "\n\033[36m|  " . strtr($string, array("\n" => "\n|  ")) . "\033[0m\n\n";
    }
}
