<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Context\Attribute;

use Behat\Behat\Context\Annotation\DocBlockHelper;
use Behat\Behat\Context\Attribute\AttributeReader;
use Behat\Hook\AfterFeature;
use Behat\Hook\AfterScenario;
use Behat\Hook\AfterStep;
use Behat\Hook\BeforeFeature;
use Behat\Hook\BeforeScenario;
use Behat\Hook\BeforeStep;
use Behat\Hook\Hook;
use ReflectionMethod;

final class HookAttributeReader implements AttributeReader
{
    /**
     * @var string[]
     */
    private const KNOWN_ATTRIBUTES = array(
        AfterFeature::class => 'Behat\Behat\Hook\Call\AfterFeature',
        AfterScenario::class => 'Behat\Behat\Hook\Call\AfterScenario',
        AfterStep::class => 'Behat\Behat\Hook\Call\AfterStep',
        BeforeFeature::class => 'Behat\Behat\Hook\Call\BeforeFeature',
        BeforeScenario::class => 'Behat\Behat\Hook\Call\BeforeScenario',
        BeforeStep::class => 'Behat\Behat\Hook\Call\BeforeStep',
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
        $attributes = $method->getAttributes(Hook::class, \ReflectionAttribute::IS_INSTANCEOF);

        $callees = [];
        foreach ($attributes as $attribute) {
            $class = self::KNOWN_ATTRIBUTES[$attribute->getName()];
            $callable = array($contextClass, $method->getName());
            $description = null;
            if ($docBlock = $method->getDocComment()) {
                $description = $this->docBlockHelper->extractDescription($docBlock);
            }

            $callees[] = new $class($attribute->newInstance()->filterString, $callable, $description);
        }

        return $callees;
    }
}
