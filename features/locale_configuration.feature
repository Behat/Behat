Feature: Locale configuration
  In order to display feature in custom language
  As a feature writer
  I need to be able to the locale inside the configuration file

  Background:
    Given I initialise the working directory from the "LocaleConfiguration" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: French locale
    When I run behat with the following additional options:
      | option                 | value              |
      | --config               | behat-french.php   |
    Then it should pass with:
      """
      Pas de scénario
      Pas d'étape
      """

  Scenario: English locale
    When I run behat with the following additional options:
      | option                 | value              |
      | --config               | behat-english.php  |
    Then it should pass with:
      """
      No scenarios
      No steps
      """
