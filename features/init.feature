Feature: Init
  In order to be able to start fast
  As a feature developer
  I need to be able to init Behat path structure fast

  Scenario: Simple init
    Given I am in the "init_test" path
    When I run "behat --init"
    Then it should pass with:
      """
      +d features - place your *.feature files here
      +d features/steps - place step definition files here
      +f features/steps/steps.php - place some step definitions in this file
      +d features/support - place support scripts and static files here
      +f features/support/bootstrap.php - place bootstrap scripts in this file
      +f features/support/env.php - place environment initialization scripts in this file
      """
    And file "features/steps/steps.php" should exist
    And file "features/support/bootstrap.php" should exist
    And file "features/support/env.php" should exist

  Scenario: In features path init
    Given I am in the "init_test/features" path
    When I run "behat --init"
    Then it should pass with:
      """
      +d steps - place step definition files here
      +f steps/steps.php - place some step definitions in this file
      +d support - place support scripts and static files here
      +f support/bootstrap.php - place bootstrap scripts in this file
      +f support/env.php - place environment initialization scripts in this file
      """
    And file "steps/steps.php" should exist
    And file "support/bootstrap.php" should exist
    And file "support/env.php" should exist

  Scenario: In features path init
    Given I am in the "init_test" path
    When I run "behat --init public/behat/features/"
    Then it should pass with:
      """
      +d public/behat/features - place your *.feature files here
      +d public/behat/features/steps - place step definition files here
      +f public/behat/features/steps/steps.php - place some step definitions in this file
      +d public/behat/features/support - place support scripts and static files here
      +f public/behat/features/support/bootstrap.php - place bootstrap scripts in this file
      +f public/behat/features/support/env.php - place environment initialization scripts in this file
      """
    And file "public/behat/features/steps/steps.php" should exist
    And file "public/behat/features/support/bootstrap.php" should exist
    And file "public/behat/features/support/env.php" should exist

  Scenario: Custom paths
    Given I am in the "init_test2" path
    And a file named "behat.yml" with:
      """
      default:
        paths:
          features: scenarios
          support:  support
          steps:
            - definitions
      """
    When I run "behat --init"
    Then it should pass with:
      """
      +d scenarios - place your *.feature files here
      +d definitions - place step definition files here
      +f definitions/steps.php - place some step definitions in this file
      +d support - place support scripts and static files here
      +f support/bootstrap.php - place bootstrap scripts in this file
      +f support/env.php - place environment initialization scripts in this file
      """
    And file "definitions/steps.php" should exist
    And file "support/bootstrap.php" should exist
    And file "support/env.php" should exist
