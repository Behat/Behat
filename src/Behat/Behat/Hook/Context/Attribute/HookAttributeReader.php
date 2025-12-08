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
use Behat\Hook\AfterSuite;
use Behat\Hook\BeforeFeature;
use Behat\Hook\BeforeScenario;
use Behat\Hook\BeforeStep;
use Behat\Hook\BeforeSuite;
use Behat\Hook\Hook;
use Behat\Testwork\Hook\Call\RuntimeHook;
use ReflectionAttribute;
use ReflectionMethod;

final class HookAttributeReader implements AttributeReader
{
    /**
     * @var array<class-string<Hook>, class-string<RuntimeHook>>
     */
    private const KNOWN_ATTRIBUTES = [
        AfterFeature::class => \Behat\Behat\Hook\Call\AfterFeature::class,
        AfterScenario::class => \Behat\Behat\Hook\Call\AfterScenario::class,
        AfterStep::class => \Behat\Behat\Hook\Call\AfterStep::class,
        BeforeFeature::class => \Behat\Behat\Hook\Call\BeforeFeature::class,
        BeforeScenario::class => \Behat\Behat\Hook\Call\BeforeScenario::class,
        BeforeStep::class => \Behat\Behat\Hook\Call\BeforeStep::class,
        BeforeSuite::class => \Behat\Testwork\Hook\Call\BeforeSuite::class,
        AfterSuite::class => \Behat\Testwork\Hook\Call\AfterSuite::class,
    ];

    /**
     * Initializes reader.
     */
    public function __construct(
        private readonly DocBlockHelper $docBlockHelper,
    ) {
    }

    /**
     * @return list<RuntimeHook>
     */
    public function readCallees(string $contextClass, ReflectionMethod $method): array
    {
        $attributes = $method->getAttributes(Hook::class, ReflectionAttribute::IS_INSTANCEOF);

        $callees = [];
        foreach ($attributes as $attribute) {
            $class = self::KNOWN_ATTRIBUTES[$attribute->getName()];
            $callable = [$contextClass, $method->getName()];
            $description = null;
            if ($docBlock = $method->getDocComment()) {
                $description = $this->docBlockHelper->extractDescription($docBlock);
            }

            $callees[] = new $class($attribute->newInstance()->getFilterString(), $callable, $description);
        }

        return $callees;
    }
}
