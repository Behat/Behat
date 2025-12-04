<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Hook\BeforeFeature;
use Behat\Hook\BeforeSuite;
use Behat\Step\Then;
use PHPUnit\Framework\Assert;

class FeatureContext implements Context
{
    #[BeforeSuite]
    public static function beforeSuiteEnableNativeAssert(): void
    {
        // Enforce native assertions during this run, so we can use them to check state without using PHPUnit classes.
        ini_set('assert.active', true);
        ini_set('assert.exception', true);
    }

    #[BeforeFeature('@phpunit_10_broken')]
    public static function beforeFeatureBreakPHPUnit10(): void
    {
        // Note this test proves both that we're handling exceptions, and that Behat will use the PHPUnit 10
        // ThrowableToStringMapper class if it's present - even though at the moment we're installing PHPUnit 9.
        static::assertClassNotLoaded(PHPUnit\Util\ThrowableToStringMapper::class);
        require_once __DIR__ . '/IncompatibleThrowableToStringMapper.php';
        class_alias(IncompatibleThrowableToStringMapper::class, PHPUnit\Util\ThrowableToStringMapper::class);
    }

    #[BeforeFeature('@phpunit_incompatible')]
    public static function beforeFeatureRemoveKnownPHPUnit(): void
    {
        // At the start of the feature, this Behat process should not have referenced any PHPUnit classes.
        // So the easiest way to simulate an incompatible PHPUnit version is to wrap the registered autoloader(s)
        // and prevent PHP from finding the exception formatting classes we support.
        // This will only affect the Behat process we're testing - the outer test runner will find them as usual.
        static::assertClassNotLoaded(PHPUnit\Util\ThrowableToStringMapper::class);
        static::assertClassNotLoaded(PHPUnit\Framework\TestFailure::class);

        if (PHP_VERSION_ID < 80400) {
            // Trigger loading array_find from symfony/polyfill-php84 before our autoloader tries to use it
            array_find([], fn () => true);
        }

        $autoloaders = spl_autoload_functions();
        array_walk($autoloaders, fn ($l) => spl_autoload_unregister($l));

        spl_autoload_register(
            function (string $class) use ($autoloaders) {
                return match ($class) {
                    PHPUnit\Framework\TestFailure::class => null,
                    PHPUnit\Util\ThrowableToStringMapper::class => null,
                    default => array_find($autoloaders, fn ($loader) => $loader($class)),
                };
            },
        );
    }

    private static function assertClassNotLoaded(string $class): void
    {
        assert(!class_exists($class, autoload: false), 'Should not have already loaded ' . $class);
    }

    #[Then('/^an array (?P<actual_json>.+?) should equal (?P<expected_json>.+)$/')]
    public function arrayShouldMatch(string $actual_json, string $expected_json): void
    {
        // To prove the output with more complex diffs
        Assert::assertEquals(
            json_decode($expected_json, true),
            json_decode($actual_json, true),
            'Should get the right value'
        );
    }

    #[Then('an integer :actual should equal :expected')]
    public function intShouldMatch(int $actual, int $expected): void
    {
        Assert::assertSame($expected, $actual, 'check the ints');
    }
}
