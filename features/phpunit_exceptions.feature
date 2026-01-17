Feature: Stringifying PHPUnit exceptions
  In order to understand why a step has failed
  As a feature developer
  I need to see the details of failed PHPUnit assertions if I am using a supported version

  Background:
    Given I initialise the working directory from the "PhpunitExceptions" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value    |
      | --no-colors |          |
      | --format    | progress |

  Scenario: With PHPUnit 9 working correctly
    When I run "behat features/with_phpunit_9.feature"
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
    When I run "behat features/with_phpunit_10_broken.feature"
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
                 [RuntimeException] Some internal problem at features/bootstrap/IncompatibleThrowableToStringMapper.php:XX

      002 Scenario: Compare mismatched ints  # features/with_phpunit_10_broken.feature:13
            Then an integer 1 should equal 2 # features/with_phpunit_10_broken.feature:14
              check the ints
              Failed asserting that 1 is identical to 2.
              !! There was an error trying to render more details of this PHPUnit\Framework\ExpectationFailedException.
                 You are probably using a PHPUnit version that Behat cannot automatically display failures for.
                 See Behat\Testwork\Exception\Stringer\PHPUnitExceptionStringer for details of PHPUnit support.
                 [RuntimeException] Some internal problem at features/bootstrap/IncompatibleThrowableToStringMapper.php:XX

      4 scenarios (2 passed, 2 failed)
      4 steps (2 passed, 2 failed)
      """

    Scenario: With unsupported PHPUnit
    When I run "behat features/with_unknown_phpunit_version.feature"
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
