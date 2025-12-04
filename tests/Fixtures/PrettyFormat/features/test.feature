Feature: Customer can see the cost of their purchase in basket
  In order to see the cost of my purchase
  As a customer
  I need to see the totals of my basket

  Background:
    Given there are the following products in the catalog
      | name     | price |
      | trousers | 12    |

  Scenario: £12 delivery £3
    Given I have an empty basket
    When I add the product "trousers" to my basket

  Scenario: £12 delivery £3
    Given I have an empty basket
    When I add the product "trousers" to my basket
