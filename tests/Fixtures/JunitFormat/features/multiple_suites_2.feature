Feature: Adding difficult numbers
  In order to add numbers together
  As an old man
  I want something that acts like a calculator for difficult sums

  Background:
    Given I have entered 131

  Scenario: Difficult sum
    When I add 247
    Then I must have 477
