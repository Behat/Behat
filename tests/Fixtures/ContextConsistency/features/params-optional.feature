Feature: Context parameters
  In order to run a browser
  As feature runner
  I need to be able to configure behat context

  Scenario: I'm little hungry
    Then context parameter "parameter1" should be equal to "val_one"
    Then context parameter "parameter2" should be equal to "val2_default"
