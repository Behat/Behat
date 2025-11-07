Feature: Show output
  In order to see the stdout output of the code being tested
  As a feature developer
  I need to be able to set if this output will be shown or not

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Step\When;

      class FeatureContext implements Context
      {
          #[When('I have a step that has no output and passes')]
          public function passingWithoutOutput() {
          }

          #[When('I have a step that shows some output and passes')]
          public function passingWithOutput() {
              echo "This step has some output";
          }

          #[When('I have a step that shows some output and fails')]
          public function failingWithOutput() {
              echo "This step also has output";
              throw new Exception("step failed as supposed");
          }
      }
      """
    And a file named "features/show_output.feature" with:
      """
      Feature: Steps with output
        In order to test the show output feature
        As a behat developer
        I need to have some steps that have some output

        Scenario: Some steps with output
          When I have a step that has no output and passes
          And I have a step that shows some output and passes
          And I have a step that shows some output and fails
      """

  Scenario: Pretty printer prints all output by default
    When I run "behat --no-colors --format=pretty --format-settings='{\"paths\": false}'"
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
    When I run "behat --no-colors --format=pretty --format-settings='{\"paths\": false, \"show_output\": \"no\" }'"
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
    When I run "behat --no-colors --format=pretty --format-settings='{\"paths\": false, \"show_output\": \"on-fail\" }'"
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
    When I run "behat --no-colors --format=progress"
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
    When I run "behat --no-colors --format=progress --format-settings='{\"show_output\": \"no\" }'"
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
    When I run "behat --no-colors --format=progress --format-settings='{\"show_output\": \"yes\" }'"
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
    When I run "behat --no-colors --format=progress --format-settings='{\"show_output\": \"on-fail\" }'"
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
    Given a file named "behat.php" with:
      """
      <?php

      use Behat\Config\Config;
      use Behat\Config\Profile;
      use Behat\Config\Formatter\ProgressFormatter;
      use Behat\Config\Formatter\ShowOutputOption;

      $profile = (new Profile('default'))
        ->withFormatter(new ProgressFormatter(showOutput: ShowOutputOption::No))
      ;

      return (new Config())->withProfile($profile);

      """
    When I run "behat --no-colors --format=progress"
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
