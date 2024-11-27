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
use Behat\Behat\Transformation\Context\Factory\TransformationCalleeFactory;
use Behat\Behat\Transformation\Transformation;
use Behat\Transformation as Attribute;
use ReflectionMethod;

/**
 * Reads transformation Attributes from the context class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TransformationAttributeReader implements AttributeReader
{
    public function __construct(
        private DocBlockHelper $docBlockHelper
    ) {
    }

    /**
     * @return Transformation[]
     */
    public function readCallees(string $contextClass, ReflectionMethod $method): array
    {
        $attributes = $method->getAttributes(Attribute\Transform::class);

        $callees = [];
        foreach ($attributes as $attribute) {
            $pattern = $attribute->newInstance()->pattern;

            $description = null;
            if ($docBlock = $method->getDocComment()) {
                $description = $this->docBlockHelper->extractDescription($docBlock);
            }

            $callees[] = TransformationCalleeFactory::create($contextClass, $method, $pattern, $description);
        }

        return $callees;
    }
}
