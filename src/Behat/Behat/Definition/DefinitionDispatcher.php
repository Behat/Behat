<?php

namespace Behat\Behat\Definition;

use Behat\Gherkin\Node\StepNode,
    Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Behat\Behat\Definition\Loader\LoaderInterface,
    Behat\Behat\Exception\Redundant,
    Behat\Behat\Exception\Ambiguous,
    Behat\Behat\Exception\Undefined;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Definition dispatcher.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DefinitionDispatcher
{
    protected $resources        = array();
    protected $loaders          = array();

    protected $transformations  = array();
    protected $definitions      = array();

    /**
     * Add a loader.
     *
     * @param   string          $format     the name of the loader
     * @param   LoaderInterface $loader     a LoaderInterface instance
     */
    public function addLoader($format, LoaderInterface $loader)
    {
        $this->loaders[$format] = $loader;
    }

    /**
     * Add a resource.
     *
     * @param   string          $format     format of the loader
     * @param   mixed           $resource   the resource name
     */
    public function addResource($format, $resource)
    {
        $this->resources[] = array($format, $resource);
    }

    /**
     * Parse step definitions with added loaders. 
     */
    protected function loadDefinitions()
    {
        if (count($this->definitions)) {
            return;
        }

        foreach ($this->resources as $resource) {
            if (!isset($this->loaders[$resource[0]])) {
                throw new \RuntimeException(
                    sprintf('The "%s" step definition loader is not registered.', $resource[0])
                );
            }

            $objects = $this->loaders[$resource[0]]->load($resource[1]);

            foreach ($objects as $object) {
                if ($object instanceof Definition) {
                    if (isset($this->definitions[$object->getRegex()])) {
                        throw new Redundant($object, $this->definitions[$object->getRegex()]);
                    }
                    $this->definitions[$object->getRegex()] = $object;
                } elseif ($object instanceof Transformation) {
                    $this->transformations[$object->getRegex()] = $object;
                }
            }
        }
    }

    /**
     * Propose step definition for step node.
     *
     * @param   StepNode    $step   step node
     * 
     * @return  array               associative array of (md5_key => definition)
     */
    public function proposeDefinition(StepNode $step)
    {
        $text = $step->getText();

        $regexp = preg_replace(
            array('/\'([^\']*)\'/', '/\"([^\"]*)\"/', '/(\d+)/'),
            array("\'([^\']*)\'", "\"([^\"]*)\"", "(\\d+)"),
            $text, -1, $count
        );

        $args = array("\$world");
        for ($i = 0; $i < $count; $i++) {
            $args[] = "\$arg".($i + 1);
        }

        foreach ($step->getArguments() as $argument) {
            if ($argument instanceof PyStringNode) {
                $args[] = "\$string";
            } elseif ($argument instanceof TableNode) {
                $args[] = "\$table";
            }
        }

        $description = sprintf(<<<PHP
\$steps->%s('/^%s$/', function(%s) {
    throw new \Behat\Behat\Exception\Pending();
});
PHP
          , '%s', $regexp, implode(', ', $args)
        );

        return array(
            md5($description) => sprintf($description, str_replace(' ', '_', $step->getType()))
        );
    }

    /**
     * Find step definition, that match specified step.
     *
     * @param   StepNode     $step       step
     * 
     * @return  Definition
     * 
     * @throws  Behat\Behat\Exception\Ambiguous  if step description is ambiguous
     * @throws  Behat\Behat\Exception\Undefined  if step definition not found
     */
    public function findDefinition(StepNode $step)
    {
        if (!count($this->definitions)) {
            $this->loadDefinitions();
        }

        $text       = $step->getText();
        $args       = $step->getArguments();
        $matches    = array();

        // find step to match
        foreach ($this->definitions as $regex => $definition) {
            if (preg_match($regex, $text, $arguments)) {
                $arguments = array_merge(array_slice($arguments, 1), $args);
 
                // transform arguments
                foreach ($this->transformations as $transformation) {
                    foreach ($arguments as $num => $argument) {
                        if ($newArgument = $transformation->transform($argument)) {
                            $arguments[$num] = $newArgument;
                        }
                    }
                }

                // set matched definition
                $definition->setMatchedText($text);
                $definition->setValues($arguments);
                $matches[] = $definition;
            }
        }

        if (count($matches) > 1) {
            throw new Ambiguous($text, $matches);
        }

        if (0 === count($matches)) {
            throw new Undefined($text);
        }

        return $matches[0];
    }
}
