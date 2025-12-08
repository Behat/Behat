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
use Behat\Behat\Context\Initializer\ContextInitializer;
use Behat\Testwork\Argument\ArgumentOrganiser;
use Behat\Testwork\Argument\Validator;
use ReflectionClass;

/**
 * Instantiates contexts using registered argument resolvers and context initializers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ContextFactory
{
    /**
     * @var ArgumentResolver[]
     */
    private $argumentResolvers = [];
    /**
     * @var ContextInitializer[]
     */
    private $contextInitializers = [];
    /**
     * @var Validator
     */
    private $validator;

    /**
     * Initialises factory.
     */
    public function __construct(
        private readonly ArgumentOrganiser $argumentOrganiser,
    ) {
        $this->validator = new Validator();
    }

    /**
     * Registers context argument resolver.
     */
    public function registerArgumentResolver(ArgumentResolver $resolver): void
    {
        $this->argumentResolvers[] = $resolver;
    }

    /**
     * Registers context initializer.
     */
    public function registerContextInitializer(ContextInitializer $initializer): void
    {
        $this->contextInitializers[] = $initializer;
    }

    /**
     * Creates and initializes context class.
     *
     * @param string             $class
     * @param ArgumentResolver[] $singleUseResolvers
     *
     * @return Context
     */
    public function createContext($class, array $arguments = [], array $singleUseResolvers = [])
    {
        $reflection = new ReflectionClass($class);
        $resolvers = array_merge($singleUseResolvers, $this->argumentResolvers);
        $resolvedArguments = $this->resolveArguments($reflection, $arguments, $resolvers);
        $context = $this->createInstance($reflection, $resolvedArguments);
        $this->initializeInstance($context);

        return $context;
    }

    /**
     * Resolves arguments for a specific class using registered argument resolvers.
     *
     * @param ArgumentResolver[] $resolvers
     *
     * @return mixed[]
     */
    private function resolveArguments(ReflectionClass $reflection, array $arguments, array $resolvers)
    {
        $newArguments = $arguments;

        foreach ($resolvers as $resolver) {
            $newArguments = $resolver->resolveArguments($reflection, $newArguments);
        }

        if (!$reflection->hasMethod('__construct')) {
            return $newArguments;
        }

        $constructor = $reflection->getConstructor();
        $newArguments = $this->argumentOrganiser->organiseArguments($constructor, $newArguments);
        $this->validator->validateArguments($constructor, $newArguments);

        return $newArguments;
    }

    /**
     * Creates context instance.
     */
    private function createInstance(ReflectionClass $reflection, array $arguments)
    {
        if (count($arguments)) {
            return $reflection->newInstanceArgs(array_values($arguments));
        }

        return $reflection->newInstance();
    }

    /**
     * Initializes context class and returns new context instance.
     */
    private function initializeInstance(Context $context): void
    {
        foreach ($this->contextInitializers as $initializer) {
            $initializer->initializeContext($context);
        }
    }
}
