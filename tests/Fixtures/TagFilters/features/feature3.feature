Feature: Feature N3

  Background:
    Given Some normal step N21

  @slow
  Scenario Outline:
    Given Some slow step N<num>

    Examples:
      | num |
      | 31  |
      | 32  |

  @normal
  Scenario:
    Given Some normal step N38

  @fast
  Scenario Outline:
    Given Some fast step N<num>

    Examples:
      | num |
      | 33  |
      | 34  |

  @normal @fast
  Scenario Outline:
    Given Some normal step N<num>
    And Some fast step N37

    Examples:
      | num |
      | 35  |
      | 36  |
