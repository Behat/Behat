Feature: Init
  In order to be able to start fast
  As a feature developer
  I need to be able to init Behat path structure fast

  Scenario: Simple init
    Given I am in the "init_test" path
    When I run "behat --no-ansi --init"
    Then it should pass with:
      """
      +d features - place your *.feature files here
      +f features/bootstrap/FeatureContext.php - place your definitions here
      """
    And file "features/bootstrap/FeatureContext.php" should exist

  Scenario: Custom paths
    Given I am in the "init_test2" path
    And a file named "behat.yml" with:
      """
      default:
        autoload: %paths.base%/supp
        suites:
          default:
            path:    %paths.base%/scenarios
            context: CustomContext
      """
    When I run "behat --no-ansi --init"
    Then it should pass with:
      """
      +d scenarios - place your *.feature files here
      +f supp/CustomContext.php - place your definitions here
      """
    And file "supp/CustomContext.php" should exist

  Scenario: Multiple suites
    Given I am in the "init_test3" path
    And a file named "behat.yml" with:
      """
      default:
        autoload: %paths.base%/contexts
        suites:
          suite1:
            path:    %paths.base%/scenarios1
            context: Custom1Context
          suite2:
            path:    %paths.base%/scenarios2
            context: Custom2Context
      """
    When I run "behat --no-ansi --init"
    Then it should pass with:
      """
      +d scenarios1 - place your *.feature files here
      +f contexts/Custom1Context.php - place your definitions here
      +d scenarios2 - place your *.feature files here
      +f contexts/Custom2Context.php - place your definitions here
      """
    And file "contexts/Custom1Context.php" should exist
    And file "contexts/Custom2Context.php" should exist
