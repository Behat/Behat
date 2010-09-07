Feature: Step Arguments
  In order to write extended steps
  As a feature writer
  I need ability to specify Table & PyString arguments to steps

  Background:
    Given a standard Behat project directory structure
    And a file named "features/support/env.php" with:
      """
      <?php
      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';

      $this->strings[1] = "hello,\n  w\n   o\nr\nl\n   d";
      $this->tables[1]  = array(
        array('item1' => 'super', 'item2' => 'mega', 'item3' => 'extra'),
        array('item1' => 'hyper', 'item2' => 'mini', 'item3' => 'XXL'),
      );
      """
    And a file named "features/steps/arguments.php" with:
      """
      <?php
      $steps->Given('/^a pystring:$/', function($world, $string) {
          $world->input = $string;
      });
      $steps->Given('/^a table:$/', function($world, $table) {
          $world->input = $table;
      });
      $steps->Then('/^it must be equals to string (\d+)$/', function($world, $arg1) {
          assertEquals($world->strings[intval($arg1)], $world->input);
      });
      $steps->Then('/^it must be equals to table (\d+)$/', function($world, $arg1) {
          assertEquals($world->tables[intval($arg1)], $world->input);
      });
      """

  Scenario: PyStrings
    Given a file named "features/pystring.feature" with:
      """
      Feature: PyStrings
        Scenario:
          Given a pystring:
            '''
            hello,
              w
               o
          r
           l
               d
            '''
          Then it must be equals to string 1
      """
    When I run "behat -f progress features/pystring.feature"
    Then it should pass with:
      """
      ..
      
      1 scenarios (1 passed)
      2 steps (2 passed)
      """

  Scenario: Table
    Given a file named "features/table.feature" with:
      """
      Feature: Tables
        Scenario:
          Given a table:
            | item1 | item2 | item3 |
            | super | mega  | extra |
            | hyper | mini  | XXL   |
          Then it must be equals to table 1
      """
    When I run "behat -f progress features/table.feature"
    Then it should pass with:
      """
      ..

      1 scenarios (1 passed)
      2 steps (2 passed)
      """
