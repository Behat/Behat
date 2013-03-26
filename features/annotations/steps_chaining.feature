Feature: Call step in other step
  In order to mantain fluid step definition
  As a features writer
  I need to be able to call other steps from step body

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\TranslatedContextInterface,
          Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException,
          Behat\Behat\Context\Step;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;
      use Symfony\Component\Finder\Finder;

      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';

      class FeatureContext extends BehatContext implements TranslatedContextInterface
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
           * @Given /I calculate "([^"]*)" and "([^"]*)"/
           */
          public function calcNumbers($number1, $number2)
          {
              return array(
                  function() use($number1, $number2) {
                      return array(
                        new Step\Given("Ввожу \"$number1\""),
                        function() use($number2) { return new Step\Given("Ввожу \"$number2\""); }
                      );

                  },
                  new Step\When("Нажимаю плюс"),
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

          public function getTranslationResources() {
              return array(__DIR__ . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'ru.xliff');
          }
      }
      """
    And a file named "features/bootstrap/i18n/ru.xliff" with:
      """
      <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
        <file original="global" source-language="en" target-language="ru" datatype="plaintext">
          <header />
          <body>
            <trans-unit id="i-calculate">
              <source>/I calculate "([^"]*)" and "([^"]*)"/</source>
              <target>/Я сложу числа "([^"]*)" и "([^"]*)"/</target>
            </trans-unit>
            <trans-unit id="i-have-entered">
              <source>/I have entered "([^"]*)"/</source>
              <target>/Ввожу "([^"]*)"/</target>
            </trans-unit>
            <trans-unit id="i-press-plus">
              <source>/I press +/</source>
              <target>/Нажимаю плюс/</target>
            </trans-unit>
          </body>
        </file>
      </xliff>
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
    When I run "behat --no-ansi -f progress features/calc_en.feature"
    Then it should fail with:
      """
      ........F

      (::) failed steps (::)

      01. Failed asserting that 8 matches expected '10'.
          In step `Then I entered "5" and expect "10"'. # FeatureContext::complexStep()
          From scenario ***.                            # features/calc_en.feature:13
          Of feature `Basic calculator'.                # features/calc_en.feature

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
    When I run "behat --no-ansi -f progress features/calc_en.feature"
    Then it should fail with:
      """
      ..F

      (::) failed steps (::)

      01. Failed asserting that 7 matches expected '8'.
          In step `Then I should see "8" on the screen'. # FeatureContext::iShouldSeeEn()
          From scenario ***.                             # features/calc_en.feature:2
          Of feature `Basic calculator'.                 # features/calc_en.feature

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
    When I run "behat --no-ansi -f progress features/calc_ru.feature"
    Then it should fail with:
      """
      .....F

      (::) failed steps (::)

      01. Undefined step "non-existent step"
          In step `И Вызовем несуществующий шаг'. # FeatureContext::assertUnexistentStepRu()
          From scenario ***.                      # features/calc_ru.feature:3
          Of feature `Стандартный калькулятор'.   # features/calc_ru.feature

      1 scenario (1 failed)
      6 steps (5 passed, 1 failed)
      """

  Scenario: Substeps i18n
    Given a file named "features/calc_ru.feature" with:
      """
      # language: ru
      Функционал: Стандартный калькулятор
        Сценарий:
          Если Я сложу числа "12" и "27"
          То Я должен увидеть на экране "39"
      """
    When I run "behat --no-ansi -f progress features/calc_ru.feature"
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
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
    When I run "behat --no-ansi --no-paths features/calc_ru.feature"
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
    When I run "behat --no-ansi -f progress features/calc_ru.feature"
    Then it should fail with:
      """
      ..FF

      (::) failed steps (::)

      01. Failed asserting that 7 matches expected '8'.
          In step `То Я должен увидеть на экране "8"'. # FeatureContext::iShouldSeeRu()
          From scenario ***.                           # features/calc_ru.feature:3
          Of feature `Стандартный калькулятор'.        # features/calc_ru.feature

      02. Failed asserting that two arrays are equal.
          --- Expected
          +++ Actual
          @@ @@
           Array (
          -    'username' => 'everzet'
          -    'password' => 'qwerty'
          +    'username' => 'antono'
          +    'password' => '123'
           )
          In step `Допустим Я создам себе failing таблицу'. # FeatureContext::assertFailingTableRu()
          From scenario ***.                                # features/calc_ru.feature:8
          Of feature `Стандартный калькулятор'.             # features/calc_ru.feature

      2 scenarios (2 failed)
      4 steps (2 passed, 2 failed)
      """
