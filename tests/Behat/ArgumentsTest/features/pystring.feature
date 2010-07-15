@feature @fast
Feature: A py string feature

  @string @slow @everzet
  Scenario: 
    When I enter a string
   """
      a string
     with something
  be
 a
   u
     ti
       ful
   """
    Then String must be
    """
       a string
      with something
  be
 a
    u
      ti
        ful
    """
