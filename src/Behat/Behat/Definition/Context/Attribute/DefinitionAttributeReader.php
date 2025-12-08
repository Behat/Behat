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
use Behat\Behat\Definition\Call;
use Behat\Behat\Definition\Call\Given;
use Behat\Behat\Definition\Call\Then;
use Behat\Behat\Definition\Call\When;
use Behat\Step as Attribute;
use ReflectionAttribute;
use ReflectionMethod;

/**
 * Reads definition Attributes from the context class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class DefinitionAttributeReader implements AttributeReader
{
    /**
     * @var array<class-string<Attribute\Definition>, class-string<Call\RuntimeDefinition>>
     */
    private static $attributeToCallMap = [
        Attribute\Given::class => Given::class,
        Attribute\When::class => When::class,
        Attribute\Then::class => Then::class,
    ];

    /**
     * Initializes reader.
     */
    public function __construct(
        private readonly DocBlockHelper $docBlockHelper,
    ) {
    }

    /**
     * @return list<Call\RuntimeDefinition>
     */
    public function readCallees(string $contextClass, ReflectionMethod $method): array
    {
        $attributes = $method->getAttributes(Attribute\Definition::class, ReflectionAttribute::IS_INSTANCEOF);

        $callees = [];
        foreach ($attributes as $attribute) {
            $class = self::$attributeToCallMap[$attribute->getName()];
            $callable = [$contextClass, $method->getName()];
            $description = null;
            if ($docBlock = $method->getDocComment()) {
                $description = $this->docBlockHelper->extractDescription($docBlock);
            }

            $callees[] = new $class($attribute->newInstance()->getPattern(), $callable, $description);
        }

        return $callees;
    }
}
