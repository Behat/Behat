<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context;

use Behat\Behat\Context\Argument\ArgumentResolver;

/**
 * Context constructors argument holder.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ArgumentHolder
{
    /**
     * @var ArgumentResolver[]
     */
    private $resolvers = array();
    /**
     * @var mixed[][string]
     */
    private $arguments;

    /**
     * Registers context argument resolver.
     *
     * @param ArgumentResolver $resolver
     */
    public function registerArgumentResolver(ArgumentResolver $resolver)
    {
        $this->resolvers[] = $resolver;
    }

    /**
     * Sets specific context class arguments.
     *
     * @param string  $classname
     * @param mixed[] $arguments
     */
    public function setContextArguments($classname, array $arguments)
    {
        $this->arguments[$classname] = $arguments;
    }

    /**
     * Returns arguments for a specific context class.
     *
     * @param string $classname
     *
     * @return mixed[]
     */
    public function getContextArguments($classname)
    {
        return isset($this->arguments[$classname]) ? $this->resolveArguments($classname) : array();
    }

    /**
     * Loads and resolves arguments for a specific class.
     *
     * @param string $classname
     *
     * @return mixed[]
     */
    protected function resolveArguments($classname)
    {
        $arguments = $this->arguments[$classname];
        foreach ($this->resolvers as $resolver) {
            $arguments = $resolver->resolveArguments($classname, $arguments);
        }

        return $arguments;
    }
}
