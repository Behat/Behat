Feature: Debug info
  In order to know more about current environment
  As a Behat user
  I need an ability to get debugging information

  Scenario: Debug
    When I run behat in debug mode
    Then it should pass
    And the output should contain:
      """
      --- configuration
      """
    And the output should contain:
      """
      --- extensions
      """
