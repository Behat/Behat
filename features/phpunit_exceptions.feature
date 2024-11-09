Feature: Stringifying PHPUnit exceptions
  In order to understand why a step has failed
  As a feature developer
  I need to see the details of failed PHPUnit assertions if I am using a supported version

  Background:
      Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

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
              static::assertClassNotLoaded(\PHPUnit\Util\ThrowableToStringMapper::class);
              require_once(__DIR__.'/IncompatibleThrowableToStringMapper.php');
              class_alias(IncompatibleThrowableToStringMapper::class, \PHPUnit\Util\ThrowableToStringMapper::class);
          }

          #[BeforeFeature('@phpunit_incompatible')]
          public static function beforeFeatureRemoveKnownPHPUnit(): void
          {
              // At the start of the feature, this Behat process should not have referenced any PHPUnit classes.
              // So the easiest way to simulate an incompatible PHPUnit version is to wrap the registered autoloader(s)
              // and prevent PHP from finding the exception formatting classes we support.
              // This will only affect the Behat process we're testing - the outer test runner will find them as usual.
              static::assertClassNotLoaded(\PHPUnit\Util\ThrowableToStringMapper::class);
              static::assertClassNotLoaded(\PHPUnit\Framework\TestFailure::class);

              if (PHP_VERSION_ID < 80400) {
                  // Trigger loading array_find from symfony/polyfill-php84 before our autoloader tries to use it
                  array_find([], fn() => true);
              }

              $autoloaders = spl_autoload_functions();
              array_walk($autoloaders, fn($l) => spl_autoload_unregister($l));

              spl_autoload_register(
                  function (string $class) use ($autoloaders) {
                      return match ($class) {
                          \PHPUnit\Framework\TestFailure::class => null,
                          \PHPUnit\Util\ThrowableToStringMapper::class => null,
                          default => array_find($autoloaders, fn($loader) => $loader($class))
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
      """
      And a file named "features/bootstrap/IncompatibleThrowableToStringMapper.php" with:
      """
      <?php

      class IncompatibleThrowableToStringMapper
      {
          public static function map($thing): string
          {
              // Simulates what happens if the PHPUnit ThrowableToStringMapper class does not behave / take the types
              // that we expect
              throw new RuntimeException('Some internal problem');
          }
      }
      """

  Scenario: With PHPUnit 9 working correctly
      Given a file named "features/with_phpunit_9.feature" with:
      """
      Feature: Values do not match
        In order to test the stringification of PHPUnit assertions
        As a contributor of behat
        I need to have a scenario that demonstrates failing assertions

        Scenario: Compare mismatched array
          Then an array {"value": "foo"} should equal {"value": "bar"}

        Scenario: Compare matching array
          Then an array {"value": "foo"} should equal {"value": "foo"}

        Scenario: Compare mismatched ints
          Then an integer 1 should equal 2

        Scenario: Compare matching ints
          Then an integer 1 should equal 1
      """
      When I run "behat -f progress --no-colors features/with_phpunit_9.feature"
      Then it should fail with:
      """
      --- Failed steps:

      001 Scenario: Compare mismatched array                             # features/with_phpunit_9.feature:6
            Then an array {"value": "foo"} should equal {"value": "bar"} # features/with_phpunit_9.feature:7
              Should get the right value
              Failed asserting that two arrays are equal.
              --- Expected
              +++ Actual
              @@ @@
               Array (
              -    'value' => 'bar'
              +    'value' => 'foo'
               )

      002 Scenario: Compare mismatched ints  # features/with_phpunit_9.feature:12
            Then an integer 1 should equal 2 # features/with_phpunit_9.feature:13
              check the ints
              Failed asserting that 1 is identical to 2.

      4 scenarios (2 passed, 2 failed)
      4 steps (2 passed, 2 failed)
      """

  Scenario: With a theoretically-supported PHPUnit that causes errors during stringification
      # Because the classes we're calling are marked as internal and not guaranteed to provide BC
      Given a file named "features/with_phpunit_10_broken.feature" with:
      """
      @phpunit_10_broken
      Feature: Values do not match
        In order to test the stringification of PHPUnit assertions
        As a contributor of behat
        I need to have a scenario that demonstrates failing assertions

        Scenario: Compare mismatched array
          Then an array {"value": "foo"} should equal {"value": "bar"}

        Scenario: Compare matching array
          Then an array {"value": "foo"} should equal {"value": "foo"}

        Scenario: Compare mismatched ints
          Then an integer 1 should equal 2

        Scenario: Compare matching ints
          Then an integer 1 should equal 1
      """
      When I run "behat -f progress --no-colors features/with_phpunit_10_broken.feature"
      Then it should fail with:
      """
      --- Failed steps:

      001 Scenario: Compare mismatched array                             # features/with_phpunit_10_broken.feature:7
            Then an array {"value": "foo"} should equal {"value": "bar"} # features/with_phpunit_10_broken.feature:8
              Should get the right value
              Failed asserting that two arrays are equal.
              !! There was an error trying to render more details of this PHPUnit\Framework\ExpectationFailedException.
                 You are probably using a PHPUnit version that Behat cannot automatically display failures for.
                 See Behat\Testwork\Exception\Stringer\PHPUnitExceptionStringer for details of PHPUnit support.
                 [RuntimeException] Some internal problem at features/bootstrap/IncompatibleThrowableToStringMapper.php:9

      002 Scenario: Compare mismatched ints  # features/with_phpunit_10_broken.feature:13
            Then an integer 1 should equal 2 # features/with_phpunit_10_broken.feature:14
              check the ints
              Failed asserting that 1 is identical to 2.
              !! There was an error trying to render more details of this PHPUnit\Framework\ExpectationFailedException.
                 You are probably using a PHPUnit version that Behat cannot automatically display failures for.
                 See Behat\Testwork\Exception\Stringer\PHPUnitExceptionStringer for details of PHPUnit support.
                 [RuntimeException] Some internal problem at features/bootstrap/IncompatibleThrowableToStringMapper.php:9

      4 scenarios (2 passed, 2 failed)
      4 steps (2 passed, 2 failed)
      """

    Scenario: With unsupported PHPUnit
      Given a file named "features/with_unknown_phpunit_version.feature" with:
      """
      @phpunit_incompatible
      Feature: Values do not match
        In order to test the stringification of PHPUnit assertions
        As a contributor of behat
        I need to have a scenario that demonstrates failing assertions

        Scenario: Compare mismatched array
          Then an array {"value": "foo"} should equal {"value": "bar"}

        Scenario: Compare matching array
          Then an array {"value": "foo"} should equal {"value": "foo"}

        Scenario: Compare mismatched ints
          Then an integer 1 should equal 2

        Scenario: Compare matching ints
          Then an integer 1 should equal 1
      """
      When I run "behat -f progress --no-colors features/with_unknown_phpunit_version.feature"
      Then it should fail with:
      """
      --- Failed steps:

      001 Scenario: Compare mismatched array                             # features/with_unknown_phpunit_version.feature:7
            Then an array {"value": "foo"} should equal {"value": "bar"} # features/with_unknown_phpunit_version.feature:8
              Should get the right value
              Failed asserting that two arrays are equal.
              !! Could not render more details of this PHPUnit\Framework\ExpectationFailedException.
                 Behat does not support automatically formatting assertion failures for your PHPUnit version.
                 See Behat\Testwork\Exception\Stringer\PHPUnitExceptionStringer for details.

      002 Scenario: Compare mismatched ints  # features/with_unknown_phpunit_version.feature:13
            Then an integer 1 should equal 2 # features/with_unknown_phpunit_version.feature:14
              check the ints
              Failed asserting that 1 is identical to 2.
              !! Could not render more details of this PHPUnit\Framework\ExpectationFailedException.
                 Behat does not support automatically formatting assertion failures for your PHPUnit version.
                 See Behat\Testwork\Exception\Stringer\PHPUnitExceptionStringer for details.

      4 scenarios (2 passed, 2 failed)
      4 steps (2 passed, 2 failed)
      """
