Feature: Table row argument transformation
  As a feature developer
  I want to be able to transform table rows

  Scenario:
    Given I am user:
      | username | age |
      | ever.zet | 22  |
    Then the Usernames must be:
      | username |
      | ever.zet |

  Scenario:
    Given I am user:
      | %username@ | age# |
      | rajesh     | 35   |
    Then the Usernames must be:
      | $username |
      | rajesh    |

  Scenario:
    Given I am user:
      | username | age |
      | ever.zet | 22  |
    Then the Usernames must be:
      | логин    |
      | ever.zet |
