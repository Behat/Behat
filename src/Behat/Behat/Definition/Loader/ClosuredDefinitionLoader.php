<?php

namespace Behat\Behat\Definition\Loader;

use Behat\Gherkin\Node\StepNode;

use Behat\Behat\Definition\DefinitionDispatcher,
    Behat\Behat\Definition\Annotation\Given,
    Behat\Behat\Definition\Annotation\When,
    Behat\Behat\Definition\Annotation\Then,
    Behat\Behat\Definition\Annotation\Transformation;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Closured step definitions loader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ClosuredDefinitionLoader implements DefinitionLoaderInterface
{
    private $dispatcher;

    /**
     * Initializes loader.
     *
     * @param DefinitionDispatcher $dispatcher
     */
    public function __construct(DefinitionDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Loads definitions from provided resource.
     *
     * @param mixed $resource
     */
    public function load($resource)
    {
        $steps = $this;

        require_once($resource);
    }

    /**
     * Defines argument transformation.
     *
     * @param string   $regex    transformation regex (to find specific argument)
     * @param callback $callback transformation callback (must return transformed argument)
     */
    public function Transform($regex, $callback)
    {
        $this->dispatcher->addTransformation(new Transformation($callback, $regex));
    }

    /**
     * Defines a step with ->Given|When|Then|...('/regex/', callback) or
     * call a step with ->Given|When|Then|...('I enter "12" in the field', $world) or
     * even with arguments ->Given|When|Then|...('I fill up fields', $world, $table).
     *
     * @param string $type      step type (Given|When|Then|...)
     * @param string $arguments step regex & callback
     *
     * @return ClosuredDefinitionLoader
     *
     * @throws RedundantException if step definition is already exists
     */
    public function __call($type, $arguments)
    {
        if (2 == count($arguments) && is_callable($arguments[1])) {
            switch (strtolower($type)) {
                case 'when':
                    $definition = new When($arguments[1], $arguments[0]);
                    break;
                case 'then':
                    $definition = new Then($arguments[1], $arguments[0]);
                    break;
                case 'given':
                default:
                    $definition = new Given($arguments[1], $arguments[0]);
                    break;
            }

            $this->dispatcher->addDefinition($definition);
        } else {
            $text   = array_shift($arguments);
            $world  = array_shift($arguments);

            $step   = new StepNode($type, $text);
            $step->setArguments($arguments);

            $definition = $this->dispatcher->findDefinition($world, $step);
            $definition->run($world);
        }

        return $this;
    }
}
