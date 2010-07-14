Feature: A table feature

  Scenario: 
    When I enter a table
      | item1 | item2 |
      | 10    | 12    |
      | 125   | 444   |

    Then Table must be

 | item1 | item2 |
 | 10    | 12    |
 | 125   | 444   |
