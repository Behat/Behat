Feature: Call step in other step
  In order to mantain fluid step definition
  As a features writer
  I need to be able to call other steps from step body

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException,
          Behat\Behat\Context\Step;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;
      use Symfony\Component\Finder\Finder;

      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';

      class FeatureContext extends BehatContext
      {
          private $result = 0;
          private $numbers = array();
          private $hash;

          public function __construct()
          {
              $this->hash = array('username' => 'everzet', 'password' => 'qwerty');
          }

          /**
           * @Given /I have entered "([^"]*)"/
           */
          public function iHaveEnteredEn($number)
          {
              $this->numbers[] = $number;
          }

          /**
           * @When /I press +/
           */
          public function iPressPlusEn()
          {
              $this->result  = array_sum($this->numbers);
              $this->numbers = array();
          }

          /**
           * @Then /I should see "([^"]*)" on the screen/
           */
          public function iShouldSeeEn($result)
          {
              assertEquals($result, $this->result);
          }

          /**
           * @Then /Table should be:/
           */
          public function assertTableEn(TableNode $table)
          {
              assertEquals($this->hash, $table->getRowsHash());
          }

          /**
           * @Given /Я ввел "([^"]*)"/
           */
          public function iHaveEnteredRu($number)
          {
              return new Step\Given("I have entered \"$number\"");
          }

          /**
           * @When /Я нажму +/
           */
          public function iPressPlusRu()
          {
              return new Step\When("I press +");
          }

          /**
           * @Then /Я должен увидеть на экране "([^"]*)"/
           */
          public function iShouldSeeRu($result)
          {
              return new Step\Then("I should see \"$result\" on the screen");
          }

          /**
           * @Given /I entered "([^"]*)" and expect "([^"]*)"/
           */
          public function complexStep($number, $result)
          {
              return array(
                  new Step\Given("I have entered \"$number\""),
                  new Step\When("I press +"),
                  new Step\Then("I should see \"$result\" on the screen")
              );
          }

          /**
           * @Then /Я создам себе failing таблицу/
           */
          public function assertFailingTableRu()
          {
              return new Step\Then('Table should be:', new Behat\Gherkin\Node\TableNode(<<<TABLE
                | username | antono |
                | password | 123    |
      TABLE
              ));
          }

          /**
           * @Then /Я создам себе passing таблицу/
           */
          public function assertPassingTableRu()
          {
              return new Step\Then('Table should be:', new Behat\Gherkin\Node\TableNode(<<<TABLE
                | username | everzet |
                | password | qwerty  |
      TABLE
              ));
          }

          /**
           * @Then /Вызовем несуществующий шаг/
           */
          public function assertUnexistentStepRu()
          {
              return new Step\Then('non-existent step');
          }
      }
      """

  Scenario:
    Given a file named "features/calc_en.feature" with:
      """
      Feature: Basic calculator
        Scenario:
          Given I have entered "12"
          And I have entered "27"
          And I have entered "5"
          When I press +
          Then I should see "44" on the screen

        Scenario:
          Given I have entered "23"
          Then I entered "10" and expect "33"

        Scenario:
          Given I have entered "3"
          Then I entered "5" and expect "10"
      """
    When I run "behat -f progress features/calc_en.feature"
    Then it should fail with:
      """
      ........F

      (::) failed steps (::)

      01. Failed asserting that <integer:8> is equal to <string:10>.
          In step `Then I entered "5" and expect "10"'. # FeatureContext::complexStep()
          From scenario ***.                            # features/calc_en.feature:13

      3 scenarios (2 passed, 1 failed)
      9 steps (8 passed, 1 failed)
      """

  Scenario:
    Given a file named "features/calc_en.feature" with:
      """
      Feature: Basic calculator
        Scenario:
          Given I have entered "7"
          When I press +
          Then I should see "8" on the screen
      """
    When I run "behat -f progress features/calc_en.feature"
    Then it should fail with:
      """
      ..F

      (::) failed steps (::)

      01. Failed asserting that <integer:7> is equal to <string:8>.
          In step `Then I should see "8" on the screen'. # FeatureContext::iShouldSeeEn()
          From scenario ***.                             # features/calc_en.feature:2

      1 scenario (1 failed)
      3 steps (2 passed, 1 failed)
      """

  Scenario:
    Given a file named "features/calc_ru.feature" with:
      """
      # language: ru
      Функционал: Стандартный калькулятор
        Сценарий:
          Допустим Я ввел "12"
          И Я ввел "27"
          Если Я нажму +
          То Я должен увидеть на экране "39"
          И Я создам себе passing таблицу
          И Вызовем несуществующий шаг
      """
    When I run "behat -f progress features/calc_ru.feature"
    Then it should fail with:
      """
      .....F

      (::) failed steps (::)

      01. Undefined step "non-existent step"
          In step `И Вызовем несуществующий шаг'. # FeatureContext::assertUnexistentStepRu()
          From scenario ***.                      # features/calc_ru.feature:3

      1 scenario (1 failed)
      6 steps (5 passed, 1 failed)
      """

  Scenario: Undefined substep in pretty format
    Given a file named "features/calc_ru.feature" with:
      """
      # language: ru
      Функционал: Стандартный калькулятор
        Сценарий:
          Допустим Я ввел "12"
          И Я ввел "27"
          Если Я нажму +
          То Я должен увидеть на экране "39"
          И Я создам себе passing таблицу
          И Вызовем несуществующий шаг
      """
    When I run "behat --no-paths features/calc_ru.feature"
    Then it should fail with:
      """
      Функционал: Стандартный калькулятор

        Сценарий:
          Допустим Я ввел "12"
          И Я ввел "27"
          Если Я нажму +
          То Я должен увидеть на экране "39"
          И Я создам себе passing таблицу
          И Вызовем несуществующий шаг
            Undefined step "non-existent step"

      1 scenario (1 failed)
      6 steps (5 passed, 1 failed)
      """

  Scenario:
    Given a file named "features/calc_ru.feature" with:
      """
      # language: ru
      Функционал: Стандартный калькулятор
        Сценарий:
          Допустим Я ввел "7"
          Если Я нажму +
          То Я должен увидеть на экране "8"

        Сценарий:
          Допустим Я создам себе failing таблицу
      """
    When I run "behat -f progress features/calc_ru.feature"
    Then it should fail with:
      """
      ..FF

      (::) failed steps (::)

      01. Failed asserting that <integer:7> is equal to <string:8>.
          In step `То Я должен увидеть на экране "8"'. # FeatureContext::iShouldSeeRu()
          From scenario ***.                           # features/calc_ru.feature:3

      02. Failed asserting that
          Array
          (
              [username] => antono
              [password] => 123
          )
           is equal to
          Array
          (
              [username] => everzet
              [password] => qwerty
          )
          .
          In step `Допустим Я создам себе failing таблицу'. # FeatureContext::assertFailingTableRu()
          From scenario ***.                                # features/calc_ru.feature:8

      2 scenarios (2 failed)
      4 steps (2 passed, 2 failed)
      """
