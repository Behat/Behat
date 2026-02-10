Feature: Greeting messages
  In order to be polite
  As a user
  I need to see appropriate greetings

  Scenario: Morning greeting
    Given the time is morning
    Then I should see "Good morning"
