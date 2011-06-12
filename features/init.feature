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
      +d features/support - place support scripts and static files here
      +f features/support/bootstrap.php - place your bootstrap code here
      +f features/support/FeaturesContext.php - place your feature code here
      """
    And file "features/support/bootstrap.php" should exist
    And file "features/support/FeaturesContext.php" should exist

  Scenario: In features path init
    Given I am in the "init_test/features" path
    When I run "behat --init"
    Then it should pass with:
      """
      +d support - place support scripts and static files here
      +f support/bootstrap.php - place your bootstrap code here
      +f support/FeaturesContext.php - place your feature code here
      """
    And file "support/bootstrap.php" should exist
    And file "support/FeaturesContext.php" should exist

  Scenario: In features path init
    Given I am in the "init_test" path
    When I run "behat --init public/behat/features/"
    Then it should pass with:
      """
      +d public/behat/features - place your *.feature files here
      +d public/behat/features/support - place support scripts and static files here
      +f public/behat/features/support/bootstrap.php - place your bootstrap code here
      +f public/behat/features/support/FeaturesContext.php - place your feature code here
      """
    And file "public/behat/features/support/bootstrap.php" should exist
    And file "public/behat/features/support/FeaturesContext.php" should exist

  Scenario: Custom paths
    Given I am in the "init_test2" path
    And a file named "behat.yml" with:
      """
      default:
        paths:
          features: scenarios
          support:  supp
      """
    When I run "behat --init"
    Then it should pass with:
      """
      +d scenarios - place your *.feature files here
      +d supp - place support scripts and static files here
      +f supp/bootstrap.php - place your bootstrap code here
      +f supp/FeaturesContext.php - place your feature code here
      """
    And file "supp/bootstrap.php" should exist
    And file "supp/FeaturesContext.php" should exist
