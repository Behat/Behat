Feature: ls
  In order to see the directory structure
  As a UNIX user
  I need to be able to list the current directory's contents

  Background:
    Given I have a file named "foo"

  Scenario: List 2 files in a directory
    Given I have a file named "bar"
    When I run "ls"
    Then I should see "bar" in output
    And I should see "foo" in output

  Scenario: List 1 file and 1 dir
    Given I have a directory named "dir"
    When I run "ls"
    Then I should see "dir" in output
    And I should see "foo" in output

  Scenario Outline:
    Given I have a <object> named "<name>"
    When I run "ls"
    Then I should see "<name>" in output
    And I should see "foo" in output

    Examples:
      | object    | name |
      | file      | bar  |
      | directory | dir  |
