<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Context\Attribute;

use Behat\Behat\Context\Annotation\DocBlockHelper;
use Behat\Behat\Context\Attribute\AttributeReader;
use Behat\Step\Definition;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use ReflectionMethod;

/**
 * Reads definition Attributes from the context class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class DefinitionAttributeReader implements AttributeReader
{
    /**
     * @var string[]
     */
    private static $classes = array(
        Given::class => 'Behat\Behat\Definition\Call\Given',
        When::class  => 'Behat\Behat\Definition\Call\When',
        Then::class  => 'Behat\Behat\Definition\Call\Then',
    );

    /**
     * @var DocBlockHelper
     */
    private $docBlockHelper;

    /**
     * Initializes reader.
     *
     * @param DocBlockHelper $docBlockHelper
     */
    public function __construct(DocBlockHelper $docBlockHelper)
    {
        $this->docBlockHelper = $docBlockHelper;
    }

    /**
     * @{inheritdoc}
     */
    public function readCallees(string $contextClass, ReflectionMethod $method)
    {
        if (\PHP_MAJOR_VERSION < 8) {
            return [];
        }

        /**
         * @psalm-suppress UndefinedClass (ReflectionAttribute is PHP 8.0 only)
         */
        $attributes = $method->getAttributes(Definition::class, \ReflectionAttribute::IS_INSTANCEOF);

        $callees = [];
        foreach ($attributes as $attribute) {
            $class = self::$classes[$attribute->getName()];
            $callable = array($contextClass, $method->getName());
            $description = null;
            if ($docBlock = $method->getDocComment()) {
                $description = $this->docBlockHelper->extractDescription($docBlock);
            }

            $callees[] = new $class($attribute->newInstance()->pattern, $callable, $description);
        }

        return $callees;
    }
}
