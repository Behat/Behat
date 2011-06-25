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
class BehatContext implements ContextInterface
{
    /**
     * List of subcontexts.
     *
     * @var     array
     */
    private $subcontexts = array();

    /**
     * Adds subcontext to current context.
     *
     * @param   Behat\Behat\Context\ContextInterface    $context
     */
    public function useContext(ContextInterface $context)
    {
        $this->subcontexts[] = $context;
    }

    /**
     * @see     Behat\Behat\Context\ContextInterface::getSubcontexts()
     */
    public function getSubcontexts()
    {
        return $this->subcontexts;
    }

    /**
     * @see     Behat\Behat\Context\ContextInterface::getContextByClassName()
     */
    public function getContextByClassName($className)
    {
        if (get_class($this) === $className) {
            return $this;
        }

        foreach ($this->getSubcontexts() as $subcontext) {
            if ($context = $subcontext->getContextByClassName($className)) {
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
