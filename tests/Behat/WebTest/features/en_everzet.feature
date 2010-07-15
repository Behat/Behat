Feature: Navigation on everzet.com
  As a PHP developer
  I need ability to browse everzet.com

  Scenario: Main page
    Given I am on the homepage
    When I follow "capifony 0.2.1"
    Then I should see "With database & shared folders manipulation tasks."
    And I should see "cap database:move:to_local"

  Scenario Outline: About page
    Given I am on the about page
    Then I should see "<has>"
    And I should not see "<no>"

    Examples:
      | has                       | no                  |
      | I'm Konstantin Kudryashov | I'm Dmitry Medvedev |
      | symfony PHP framework     | Ruby On Rails       |
