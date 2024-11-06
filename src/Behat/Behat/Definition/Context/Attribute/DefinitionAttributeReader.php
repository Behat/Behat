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
use Behat\Step as Attribute;
use Behat\Behat\Definition\Call;
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
    private static $attributeToCallMap = array(
        Attribute\Given::class => Call\Given::class,
        Attribute\When::class  => Call\When::class,
        Attribute\Then::class  => Call\Then::class,
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
        $attributes = $method->getAttributes(Attribute\Definition::class, \ReflectionAttribute::IS_INSTANCEOF);

        $callees = [];
        foreach ($attributes as $attribute) {
            $class = self::$attributeToCallMap[$attribute->getName()];
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
