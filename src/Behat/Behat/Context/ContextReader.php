<?php

namespace Behat\Behat\Context;

use Behat\Behat\Definition\DefinitionDispatcher,
    Behat\Behat\Definition\DefinitionInterface,
    Behat\Behat\Definition\TransformationInterface,
    Behat\Behat\Hook\HookDispatcher,
    Behat\Behat\Hook\HookInterface,
    Behat\Behat\Annotation\AnnotationInterface;

use Symfony\Component\Translation\TranslatorInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Context reader.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextReader
{
    /**
     * Context class name.
     *
     * @var     string
     */
    private $contextClassName;
    /**
     * Context initialization parameters.
     *
     * @var     array
     */
    private $parameters = array();
    /**
     * Step definitions dispatcher.
     *
     * @var     Behat\Behat\Definition\DefinitionDispatcher
     */
    private $definitionDispatcher;
    /**
     * Hooks dispatcher.
     *
     * @var     Behat\Behat\Hook\HookDispatcher
     */
    private $hookDispatcher;
    /**
     * Translator.
     *
     * @var     Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;
    /**
     * List of available annotations.
     *
     * @var     array
     */
    private $annotationClasses = array(
        'given'          => 'Behat\Behat\Definition\Annotation\Given',
        'when'           => 'Behat\Behat\Definition\Annotation\When',
        'then'           => 'Behat\Behat\Definition\Annotation\Then',
        'transform'      => 'Behat\Behat\Definition\Annotation\Transformation',
        'beforesuite'    => 'Behat\Behat\Hook\Annotation\BeforeSuite',
        'aftersuite'     => 'Behat\Behat\Hook\Annotation\AfterSuite',
        'beforefeature'  => 'Behat\Behat\Hook\Annotation\BeforeFeature',
        'afterfeature'   => 'Behat\Behat\Hook\Annotation\AfterFeature',
        'beforescenario' => 'Behat\Behat\Hook\Annotation\BeforeScenario',
        'afterscenario'  => 'Behat\Behat\Hook\Annotation\AfterScenario',
        'beforestep'     => 'Behat\Behat\Hook\Annotation\BeforeStep',
        'afterstep'      => 'Behat\Behat\Hook\Annotation\AfterStep'
    );

    /**
     * Initializes context reader.
     *
     * @param   string                                              $contextClassName       context class
     * @param   array                                               $parameters             context params
     * @param   Behat\Behat\Definition\DefinitionDispatcher         $definitionDispatcher   definitions
     * @param   Behat\Behat\Hook\HookDispatcher                     $hookDispatcher         hooks
     * @param   Symfony\Component\Translation\TranslatorInterface   $translator             translator
     */
    public function __construct($contextClassName, array $parameters = array(),
                                DefinitionDispatcher $definitionDispatcher, HookDispatcher $hookDispatcher, 
                                TranslatorInterface $translator)
    {
        $this->contextClassName     = $contextClassName;
        $this->parameters           = $parameters;
        $this->definitionDispatcher = $definitionDispatcher;
        $this->hookDispatcher       = $hookDispatcher;
        $this->translator           = $translator;

        if (!class_exists($this->contextClassName)) {
            throw new \InvalidArgumentException(sprintf(
                'Class "%s" not found', $this->contextClassName
            ));
        }

        $contextClassRefl = new \ReflectionClass($this->contextClassName);
        if (!$contextClassRefl->implementsInterface('Behat\Behat\Context\ContextInterface')) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot use class "%s" as context, as it doesn\'t implement ContextInterface',
                $this->contextClassName
            ));
        }
    }

    /**
     * Reads all definition data from main context.
     */
    public function read()
    {
        $this->readFromContext(new $this->contextClassName($this->parameters));
    }

    /**
     * Reads definition data from specific context class.
     *
     * @param   Behat\Behat\Context\ContextInterface    $context
     */
    private function readFromContext(ContextInterface $context)
    {
        $reflection = new \ReflectionObject($context);

        // read annotations
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $methodRefl) {
            foreach ($this->readMethodAnnotations($reflection->getName(), $methodRefl) as $annotation) {
                $this->processAnnotation($annotation);
            }
        }

        // read translations
        foreach ($context->getI18nResources() as $path) {
            $this->translator->addResource('xliff', $path, basename($path, '.xliff'), 'behat.definitions');
        }

        // read subcontexts
        foreach ($context->getSubcontexts() as $subcontext) {
            $this->readFromContext($subcontext);
        }
    }

    /**
     * Reads all supported method annotations.
     *
     * @param   stirng              $className  method class name
     * @param   ReflectionMethod    $method     method reflection
     */
    private function readMethodAnnotations($className, \ReflectionMethod $method)
    {
        $docBlock = $method->getDocComment();
        $annotations = array();

        if ($docBlock) {
            foreach (explode("\n", $docBlock) as $docLine) {
                $docLine = preg_replace('/^\/\*\*\s*|^\s*\*\s*|\s*\*\/$/', '', trim($docLine));

                if (preg_match('/^\@([a-zA-Z_][a-zA-Z_0-9]*)\s*(?:(.*))?$/', $docLine, $matches)) {
                    $annotationName = strtolower(isset($matches[1]) ? $matches[1] : 'unknown');

                    if (isset($this->annotationClasses[$annotationName])) {
                        $class    = $this->annotationClasses[$annotationName];
                        $callback = array($className, $method->getName());

                        if (isset($matches[2]) && !empty($matches[2])) {
                            $annotations[] = new $class($callback, $matches[2]);
                        } else {
                            $annotations[] = new $class($callback);
                        }
                    }
                }
            }
        }

        return $annotations;
    }

    /**
     * Process annotation instance.
     *
     * @param   Behat\Behat\Annotation\AnnotationInterface  $annotation
     */
    private function processAnnotation(AnnotationInterface $annotation)
    {
        if ($annotation instanceof DefinitionInterface) {
            $this->definitionDispatcher->addDefinition($annotation);
        } elseif ($annotation instanceof TransformationInterface) {
            $this->definitionDispatcher->addTransformation($annotation);
        } elseif ($annotation instanceof HookInterface) {
            $this->hookDispatcher->addHook($annotation);
        }
    }
}
