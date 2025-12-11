Feature: Syntax helpers
  In order to get syntax help
  As a feature writer
  I need to be able to print supported definitions and Gherkin keywords

  Background:
    Given I initialise the working directory from the "SyntaxHelp" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: Print story syntax
    When I run "behat --story-syntax"
    Then the output should contain:
      """
      [Business Need|Ability|Feature]: Internal operations
        In order to stay secret
        As a secret organization
        We need to be able to erase past agents' memory

        Background:
          [Given|*] there is agent A
          [And|*] there is agent B

        [Scenario|Example]: Erasing agent memory
          [Given|*] there is agent J
          [And|*] there is agent K
          [When|*] I erase agent K's memory
          [Then|*] there should be agent J
          [But|*] there should not be agent K

        [Scenario Template|Scenario Outline]: Erasing other agents' memory
          [Given|*] there is agent <agent1>
          [And|*] there is agent <agent2>
          [When|*] I erase agent <agent2>'s memory
          [Then|*] there should be agent <agent1>
          [But|*] there should not be agent <agent2>

          [Scenarios|Examples]:
            | agent1 | agent2 |
            | D      | M      |
      """

  Scenario: Print story syntax in native language
    When I run "behat --story-syntax --lang el"
    Then the output should contain:
      """
      # language: el
      [Δυνατότητα|Λειτουργία]: Internal operations
        In order to stay secret
        As a secret organization
        We need to be able to erase past agents' memory

        Υπόβαθρο:
          [Δεδομένου|*] there is agent A
          [Και|*] there is agent B

        [Παράδειγμα|Σενάριο]: Erasing agent memory
          [Δεδομένου|*] there is agent J
          [Και|*] there is agent K
          [Όταν|*] I erase agent K's memory
          [Τότε|*] there should be agent J
          [Αλλά|*] there should not be agent K

        [Περίγραμμα Σεναρίου|Περιγραφή Σεναρίου]: Erasing other agents' memory
          [Δεδομένου|*] there is agent <agent1>
          [Και|*] there is agent <agent2>
          [Όταν|*] I erase agent <agent2>'s memory
          [Τότε|*] there should be agent <agent1>
          [Αλλά|*] there should not be agent <agent2>

          [Παραδείγματα|Σενάρια]:
            | agent1 | agent2 |
            | D      | M      |
      """

  Scenario: Print available definitions
    When I run "behat --profile=definitions --definitions l"
    Then the output should contain:
      """
      default | Given /^(?:I|We) have (\d+) apples?$/
      default | When /^(?:I|We) ate (\d+) apples?$/
      default | When /^(?:I|We) found (\d+) apples?$/
      default | Then /^(?:I|We) should have (\d+) apples$/
      """

  Scenario: Print available definitions in native language
    When I run "behat --profile=translatable --definitions l --lang=ru"
    Then the output should contain:
      """
      default | Допустим /^у меня (\d+) яблоко?$/
      default | Когда /^I ate (\d+) apples?$/
      default | Когда /^Я нашел (\d+) яблоко?$/
      default | Затем /^I should have (\d+) apples$/
      """

  Scenario: Print extended definitions info
    When I run "behat --profile=descriptions --definitions i"
    Then the output should contain:
      """
      default | [Given|*] /^I have (\d+) apples?$/
              | at `DescriptionsContext::iHaveApples()`

      default | [When|*] /^I ate (\d+) apples?$/
              | Eating apples.
              | More details on eating apples, and a list:
              | - one
              | - two
              | at `DescriptionsContext::iAteApples()`

      default | [When|*] /^I found (\d+) apples?$/
              | at `DescriptionsContext::iFoundApples()`

      default | [Then|*] /^I should have (\d+) apples$/
              | at `DescriptionsContext::iShouldHaveApples()`
      """

  Scenario: Print extended definitions info with file name and line numbers
    When I run "behat --profile=descriptions --definitions i -v"
    Then the output should contain:
      """
      default | [Given|*] /^I have (\d+) apples?$/
              | at `DescriptionsContext::iHaveApples()`
              | on `%%WORKING_DIR%%features%%DS%%bootstrap%%DS%%DescriptionsContext.php[14:17]`

      default | [When|*] /^I ate (\d+) apples?$/
              | Eating apples.
              | More details on eating apples, and a list:
              | - one
              | - two
              | at `DescriptionsContext::iAteApples()`
              | on `%%WORKING_DIR%%features%%DS%%bootstrap%%DS%%DescriptionsContext.php[29:32]`

      default | [When|*] /^I found (\d+) apples?$/
              | at `DescriptionsContext::iFoundApples()`
              | on `%%WORKING_DIR%%features%%DS%%bootstrap%%DS%%DescriptionsContext.php[35:38]`

      default | [Then|*] /^I should have (\d+) apples$/
              | at `DescriptionsContext::iShouldHaveApples()`
              | on `%%WORKING_DIR%%features%%DS%%bootstrap%%DS%%DescriptionsContext.php[41:44]`
      """

  Scenario: Search definition
    When I run "behat --profile=translatable --lang=ru --definitions 'нашел'"
    Then the output should contain:
      """
      default | [Когда|Если|*] /^Я нашел (\d+) яблоко?$/
              | at `TranslatableDefinitionsContext::iFoundApples()`
      """
