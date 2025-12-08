Feature: Show output
  In order to see the stdout output of the code being tested
  As a feature developer
  I need to be able to set if this output will be shown or not

  Background:
    Given I initialise the working directory from the "ShowOutput" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: Pretty printer prints all output by default
    When I run "behat --format=pretty --format-settings='{\"paths\": false}' features/show_output.feature"
    Then it should fail with:
      """
      Feature: Steps with output
        In order to test the show output feature
        As a behat developer
        I need to have some steps that have some output

        Scenario: Some steps with output
          When I have a step that has no output and passes
          And I have a step that shows some output and passes
            │ This step has some output
          And I have a step that shows some output and fails
            │ This step also has output
            step failed as supposed (Exception)

      --- Failed scenarios:

          features/show_output.feature:6 (on line 9)

      1 scenario (1 failed)
      3 steps (2 passed, 1 failed)
      """

  Scenario: Pretty printer does not print any output if option set to "no"
    When I run "behat --format=pretty --format-settings='{\"paths\": false, \"show_output\": \"no\" }' features/show_output.feature"
    Then it should fail with:
      """
      Feature: Steps with output
        In order to test the show output feature
        As a behat developer
        I need to have some steps that have some output

        Scenario: Some steps with output
          When I have a step that has no output and passes
          And I have a step that shows some output and passes
          And I have a step that shows some output and fails
            step failed as supposed (Exception)

      --- Failed scenarios:

          features/show_output.feature:6 (on line 9)

      1 scenario (1 failed)
      3 steps (2 passed, 1 failed)
      """

  Scenario: Pretty printer only prints output on failed steps if option set to "on-fail"
    When I run "behat --format=pretty --format-settings='{\"paths\": false, \"show_output\": \"on-fail\" }' features/show_output.feature"
    Then it should fail with:
      """
      Feature: Steps with output
        In order to test the show output feature
        As a behat developer
        I need to have some steps that have some output

        Scenario: Some steps with output
          When I have a step that has no output and passes
          And I have a step that shows some output and passes
          And I have a step that shows some output and fails
            │ This step also has output
            step failed as supposed (Exception)

      --- Failed scenarios:

          features/show_output.feature:6 (on line 9)

      1 scenario (1 failed)
      3 steps (2 passed, 1 failed)
      """

  Scenario: Progress printer only prints output in summary by default
    When I run "behat --format=progress features/show_output.feature"
    Then it should fail with:
      """
      ..F

      --- Failed steps:

      001 Scenario: Some steps with output                     # features/show_output.feature:6
            And I have a step that shows some output and fails # features/show_output.feature:9
              │ This step also has output
              step failed as supposed (Exception)

      1 scenario (1 failed)
      3 steps (2 passed, 1 failed)
      """

  Scenario: Progress printer does not print any output if option set to "no"
    When I run "behat --format=progress --format-settings='{\"show_output\": \"no\" }' features/show_output.feature"
    Then it should fail with:
      """
      ..F

      --- Failed steps:

      001 Scenario: Some steps with output                     # features/show_output.feature:6
            And I have a step that shows some output and fails # features/show_output.feature:9
              step failed as supposed (Exception)

      1 scenario (1 failed)
      3 steps (2 passed, 1 failed)
      """

  Scenario: Progress printer prints all output if option set to "yes"
    When I run "behat --format=progress --format-settings='{\"show_output\": \"yes\" }' features/show_output.feature"
    Then it should fail with:
      """
      ..
      FeatureContext::passingWithOutput():
        | This step has some output
      F
      FeatureContext::failingWithOutput():
        | This step also has output

      --- Failed steps:

      001 Scenario: Some steps with output                     # features/show_output.feature:6
            And I have a step that shows some output and fails # features/show_output.feature:9
              │ This step also has output
              step failed as supposed (Exception)

      1 scenario (1 failed)
      3 steps (2 passed, 1 failed)
      """

  Scenario: Progress printer only prints output on fail if option set to "on-fail"
    When I run "behat --format=progress --format-settings='{\"show_output\": \"on-fail\" }' features/show_output.feature"
    Then it should fail with:
      """
      ..F
      FeatureContext::failingWithOutput():
        | This step also has output

      --- Failed steps:

      001 Scenario: Some steps with output                     # features/show_output.feature:6
            And I have a step that shows some output and fails # features/show_output.feature:9
              │ This step also has output
              step failed as supposed (Exception)

      1 scenario (1 failed)
      3 steps (2 passed, 1 failed)
      """

  Scenario: Check that this option can be set using the config file
    When I run "behat --profile=progress_no_output --format=progress features/show_output.feature"
    Then it should fail with:
      """
      ..F

      --- Failed steps:

      001 Scenario: Some steps with output                     # features/show_output.feature:6
            And I have a step that shows some output and fails # features/show_output.feature:9
              step failed as supposed (Exception)

      1 scenario (1 failed)
      3 steps (2 passed, 1 failed)
      """
