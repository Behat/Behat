Feature: Stringifying PHPUnit exceptions
  In order to understand why a step has failed
  As a feature developer
  I need to see the details of failed PHPUnit assertions if I am using a supported version

  Background:
    Given I initialise the working directory from the "PhpunitExceptions" fixtures folder
    And I provide the following options for all behat invocations:
      | option                     | value    |
      | --no-colors                |          |
      | --format                   | progress |
      | --print-behat-deprecations |          |

  Scenario: With a version of PHPUnit that we support, working as expected
    When I run "behat features/with_supported_phpunit.feature"
    Then it should fail with:
      """
      --- Failed steps:

      001 Scenario: Compare mismatched array                             # features/with_supported_phpunit.feature:6
            Then an array {"value": "foo"} should equal {"value": "bar"} # features/with_supported_phpunit.feature:7
              Should get the right value
              Failed asserting that two arrays are equal.
              --- Expected
              +++ Actual
              @@ @@
               Array (
              -    'value' => 'bar'
              +    'value' => 'foo'
               )

      002 Scenario: Compare mismatched ints  # features/with_supported_phpunit.feature:12
            Then an integer 1 should equal 2 # features/with_supported_phpunit.feature:13
              check the ints
              Failed asserting that 1 is identical to 2.

      4 scenarios (2 passed, 2 failed)
      4 steps (2 passed, 2 failed)

      2 deprecations triggered (1 unique):

        ⚠ Behat's built-in support for PHPUnit assertions is deprecated and will be removed in 4.0. See https://github.com/Behat/PHPUnitAssertionsExtension. (2x)
      """

  Scenario: No deprecation when the extension is enabled
    When I run "behat --profile=with-extension features/with_supported_phpunit.feature"
    # Note: we install a stub of the extension class, not the actual extension - so the Behat output doesn't actually
    # include formatted failure messages. This is enough to prove that we didn't enable the built-in stringer.
    Then it should fail with:
      """
      --- Failed steps:

      001 Scenario: Compare mismatched array                             # features/with_supported_phpunit.feature:6
            Then an array {"value": "foo"} should equal {"value": "bar"} # features/with_supported_phpunit.feature:7
              Should get the right value
              Failed asserting that two arrays are equal. (PHPUnit\Framework\ExpectationFailedException)

      002 Scenario: Compare mismatched ints  # features/with_supported_phpunit.feature:12
            Then an integer 1 should equal 2 # features/with_supported_phpunit.feature:13
              check the ints
              Failed asserting that 1 is identical to 2. (PHPUnit\Framework\ExpectationFailedException)

      4 scenarios (2 passed, 2 failed)
      4 steps (2 passed, 2 failed)
      """

  Scenario: With a theoretically-supported PHPUnit that causes errors during stringification
      # Because the classes we're calling are marked as internal and not guaranteed to provide BC
    When I run "behat features/with_phpunit_next_broken.feature"
    Then it should fail with:
      """
      --- Failed steps:

      001 Scenario: Compare mismatched array                             # features/with_phpunit_next_broken.feature:7
            Then an array {"value": "foo"} should equal {"value": "bar"} # features/with_phpunit_next_broken.feature:8
              Should get the right value
              Failed asserting that two arrays are equal.
              !! There was an error trying to render more details of this PHPUnit\Framework\ExpectationFailedException.
                 You are probably using a PHPUnit version that Behat 3.x does not support.
                 See https://github.com/Behat/PHPUnitAssertionsExtension for improved PHPUnit support.
                 [RuntimeException] Some internal problem at features/bootstrap/IncompatibleThrowableToStringMapper.php:XX

      002 Scenario: Compare mismatched ints  # features/with_phpunit_next_broken.feature:13
            Then an integer 1 should equal 2 # features/with_phpunit_next_broken.feature:14
              check the ints
              Failed asserting that 1 is identical to 2.
              !! There was an error trying to render more details of this PHPUnit\Framework\ExpectationFailedException.
                 You are probably using a PHPUnit version that Behat 3.x does not support.
                 See https://github.com/Behat/PHPUnitAssertionsExtension for improved PHPUnit support.
                 [RuntimeException] Some internal problem at features/bootstrap/IncompatibleThrowableToStringMapper.php:XX

      4 scenarios (2 passed, 2 failed)
      4 steps (2 passed, 2 failed)

      2 deprecations triggered (1 unique):

        ⚠ Behat's built-in support for PHPUnit assertions is deprecated and will be removed in 4.0. See https://github.com/Behat/PHPUnitAssertionsExtension. (2x)
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
                 Behat 3.x does not support automatically formatting assertion failures for your PHPUnit version.
                 See https://github.com/Behat/PHPUnitAssertionsExtension for improved PHPUnit support.

      002 Scenario: Compare mismatched ints  # features/with_unknown_phpunit_version.feature:13
            Then an integer 1 should equal 2 # features/with_unknown_phpunit_version.feature:14
              check the ints
              Failed asserting that 1 is identical to 2.
              !! Could not render more details of this PHPUnit\Framework\ExpectationFailedException.
                 Behat 3.x does not support automatically formatting assertion failures for your PHPUnit version.
                 See https://github.com/Behat/PHPUnitAssertionsExtension for improved PHPUnit support.

      4 scenarios (2 passed, 2 failed)
      4 steps (2 passed, 2 failed)

      2 deprecations triggered (1 unique):

        ⚠ Behat's built-in support for PHPUnit assertions is deprecated and will be removed in 4.0. See https://github.com/Behat/PHPUnitAssertionsExtension. (2x)
      """
