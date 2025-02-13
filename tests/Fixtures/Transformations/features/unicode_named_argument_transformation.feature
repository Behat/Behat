Feature: Unicode named argument transformation
  As a feature developer
  I want to be able to transform named arguments which use Unicode into Users

  Scenario:
    Given I am боб
    Then Username must be "боб"

  Scenario:
    Given I am "элис"
    Then Username must be "элис"
