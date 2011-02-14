<?php

namespace Behat\Behat\Definition;

use Symfony\Component\Translation\Translator;

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
    /**
     * Definition resources.
     *
     * @var     array
     */
    protected $resources        = array();
    /**
     * Definition resource loaders.
     *
     * @var     array
     */
    protected $loaders          = array();
    /**
     * Loaded transformations.
     *
     * @var     array
     */
    protected $transformations  = array();
    /**
     * Loaded definitions.
     *
     * @var     array
     */
    protected $definitions      = array();
    /**
     * Translator instance.
     *
     * @var     Symfony\Component\Translation\Translator
     */
    protected $translator;

    /**
     * Initializes definition dispatcher.
     *
     * @param   Symfony\Component\Translation\Translator    $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Adds a resource loader.
     *
     * @param   string                                          $format     loader format name
     * @param   Behat\Behat\Definition\Loader\LoaderInterface   $loader     loader instance
     */
    public function addLoader($format, LoaderInterface $loader)
    {
        $this->loaders[$format] = $loader;
    }

    /**
     * Adds a resource to load.
     *
     * @param   string          $format     loader format name
     * @param   mixed           $resource   the resource name
     */
    public function addResource($format, $resource)
    {
        $this->resources[] = array($format, $resource);
    }

    /**
     * Returns step definition for step node.
     *
     * @param   Behat\Gherkin\Node\StepNode     $step   step node
     *
     * @return  array   hash (md5_key => definition)
     */
    public function proposeDefinition(StepNode $step)
    {
        $text = $step->getText();

        $regex = preg_replace('/([\[\]\(\)\\\^\$\.\|\?\*\+])/', '\\\\$1', $text);
        $regex = preg_replace(
            array(
                '/\'([^\']*)\'/', '/\"([^\"]*)\"/',
                '/(\d+)/'
            ),
            array(
                "\'([^\']*)\'", "\"([^\"]*)\"",
                "(\\d+)"
            ),
            $regex, -1, $count
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
          , '%s', $regex, implode(', ', $args)
        );

        return array(
            md5($description) => sprintf($description, str_replace(' ', '_', $step->getType()))
        );
    }

    /**
     * Returns array of available definitions.
     *
     * @return  array   array of hashes => array(regex => definition)
     */
    public function getDefinitions()
    {
        if (!count($this->definitions)) {
            $this->loadDefinitions();
        }

        return $this->definitions;
    }

    /**
     * Finds step definition, that match specified step.
     *
     * @param   Behat\Gherkin\Node\StepNode     $step   found step
     *
     * @return  Behat\Behat\Definition\Definition
     *
     * @uses    loadDefinitions()
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
        foreach ($this->definitions as $origRegex => $definition) {
            $transRegex = $this->translateDefinitionRegex($origRegex, $step->getLanguage());

            if (preg_match($origRegex, $text, $arguments)
            || ($origRegex !== $transRegex && preg_match($transRegex, $text, $arguments))) {
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

    /**
     * Translates definition regex to provided language (if possible).
     *
     * @param   string  $regex      regex to translate
     * @param   string  $language   language
     * 
     * @return  string
     */
    public function translateDefinitionRegex($regex, $language)
    {
        return $this->translator->trans($regex, array(), 'behat.definitions', $language);
    }

    /**
     * Parses step definitions with added loaders.
     *
     * @throws  RuntimeException                    if loader with specified format is not registered
     * @throws  Behat\Behat\Exception\Redundant     if step definition is already exists
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
}
