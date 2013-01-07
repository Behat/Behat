<?php

namespace Behat\Behat\Context\Dispatcher;

use Behat\Behat\Context\ContextInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Context dispatcher directly instantiating new contexts.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Instantiating extends AbstractDispatcher
{
    private $parameters    = array();

    /**
     * Initialize dispatcher.
     *
     * @param array $parameters context parameters
     */
    public function __construct(array $parameters = array())
    {
        $this->parameters = $parameters;
    }

    /**
     * Returns context parameters.
     *
     * @return array
     */
    public function getContextParameters()
    {
        return $this->parameters;
    }

    /**
     * Creates new context instance.
     *
     * @return ContextInterface
     *
     * @throws \RuntimeException
     */
    public function createContext()
    {
        $classname  = $this->getContextClass();
        $parameters = $this->getContextParameters();
        $context    = new $classname($parameters);

        $this->initializeContext($context);

        return $context;
    }
}
