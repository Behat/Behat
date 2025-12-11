Feature: Ambiguous orders in coffee menu
  In order to be able to chose concrete coffee type
  As a coffee buyer
  I need to be able to know about ambiguous decisions

  Scenario: Ambiguous coffee type
    Given human have chosen "Latte"
    Then I should make him "Latte"
