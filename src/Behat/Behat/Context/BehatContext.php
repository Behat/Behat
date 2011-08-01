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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatContext implements ExtendedContextInterface
{
    /**
     * List of subcontexts.
     *
     * @var     array
     */
    private $subcontexts = array();
    /**
     * Parent context of subcontext.
     *
     * @var     Behat\Behat\Context\ContextInterface
     */
    private $parentContext;

    /**
     * Adds subcontext to current context.
     *
     * @param   string                                          $alias      subcontext alias name
     * @param   Behat\Behat\Context\ExtendedContextInterface    $context    subcontext instance
     */
    public function useContext($alias, ExtendedContextInterface $context)
    {
        $context->setParentContext($this);
        $this->subcontexts[$alias] = $context;
    }

    /**
     * @see     Behat\Behat\Context\ExtendedContextInterface::setParentContext()
     * @see     Behat\Behat\Context\BehatContext::useContext()
     */
    public function setParentContext(ExtendedContextInterface $parentContext)
    {
        $this->parentContext = $parentContext;
    }

    /**
     * @see     Behat\Behat\Context\ExtendedContextInterface::getMainContext()
     */
    public function getMainContext()
    {
        if (null !== $this->parentContext) {
            return $this->parentContext->getMainContext();
        }

        return $this;
    }

    /**
     * @see     Behat\Behat\Context\ExtendedContextInterface::getSubcontext()
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
     * @see     Behat\Behat\Context\ContextInterface::getSubcontexts()
     */
    public function getSubcontexts()
    {
        return $this->subcontexts;
    }

    /**
     * @see     Behat\Behat\Context\ContextInterface::getSubcontextByClassName()
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
     * @param     string  $string     debug string
     */
    public function printDebug($string)
    {
        echo "\n\033[36m|  " . strtr($string, array("\n" => "\n|  ")) . "\033[0m\n\n";
    }
}
