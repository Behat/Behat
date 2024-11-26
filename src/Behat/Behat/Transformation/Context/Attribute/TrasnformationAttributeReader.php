<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Transformation\Context\Attribute;

use Behat\Behat\Context\Annotation\DocBlockHelper;
use Behat\Behat\Context\Attribute\AttributeReader;
use Behat\Behat\Transformation\Context\Annotation\TransformationAnnotationReader;
use Behat\Transformation as Attribute;
use ReflectionMethod;

/**
 * Reads transformation Attributes from the context class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TrasnformationAttributeReader extends TransformationAnnotationReader implements AttributeReader
{
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
        $attributes = $method->getAttributes(Attribute\Transformation::class, \ReflectionAttribute::IS_INSTANCEOF);

        $callees = [];
        foreach ($attributes as $attribute) {
            $docLine = '@Transform';
            $pattern = $attribute->newInstance()->pattern;;
            if ($pattern !== null) {
                $docLine .= ' ' . $pattern;
            }

            $description = null;
            if ($docBlock = $method->getDocComment()) {
                $description = $this->docBlockHelper->extractDescription($docBlock);
            }

            $callee = $this->readCallee($contextClass, $method, $docLine, $description);

            if ($callee !== null) {
                $callees[] = $callee;
            }
        }

        return $callees;
    }
}
