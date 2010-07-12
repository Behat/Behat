Feature: Fibonacci
  In order to calculate super fast fibonacci series
  As a pythonista
  I want to use Python for that
  
  Scenario Outline: Series
    When I ask python to calculate fibonacci up to <n>
    Then it should give me <series>