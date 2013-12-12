Feature: Syntax helpers
  In order to get syntax help
  As a feature writer
  I need to be able to print supported definitions and Gherkin keywords

  Scenario: Print story syntax
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php class FeatureContext implements Behat\Behat\Context\Context {}
      """
    When I run "behat --no-colors --story-syntax"
    Then the output should contain:
      """
      [Feature|Business Need|Ability]: Internal operations
        In order to stay secret
        As a secret organization
        We need to be able to erase past agents' memory

        Background:
          Given there is agent A
          And there is agent B

        Scenario: Erasing agent memory
          Given there is agent J
          And there is agent K
          When I erase agent K's memory
          Then there should be agent J
          But there should not be agent K

        [Scenario Outline|Scenario Template]: Erasing other agents' memory
          Given there is agent <agent1>
          And there is agent <agent2>
          When I erase agent <agent2>'s memory
          Then there should be agent <agent1>
          But there should not be agent <agent2>

          [Examples|Scenarios]:
            | agent1 | agent2 |
            | D      | M      |
      """

  Scenario: Print story syntax in native language
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php class FeatureContext implements Behat\Behat\Context\Context {}
      """
    When I run "behat --no-colors --story-syntax --lang ru"
    Then the output should contain:
      """
      # language: ru
      [Функция|Функционал|Свойство]: Internal operations
        In order to stay secret
        As a secret organization
        We need to be able to erase past agents' memory

        [Предыстория|Контекст]:
          [Допустим|Пусть|Дано] there is agent A
          [К тому же|Также|И] there is agent B

        Сценарий: Erasing agent memory
          [Допустим|Пусть|Дано] there is agent J
          [К тому же|Также|И] there is agent K
          [Когда|Если] I erase agent K's memory
          [Тогда|То] there should be agent J
          [Но|А] there should not be agent K

        Структура сценария: Erasing other agents' memory
          [Допустим|Пусть|Дано] there is agent <agent1>
          [К тому же|Также|И] there is agent <agent2>
          [Когда|Если] I erase agent <agent2>'s memory
          [Тогда|То] there should be agent <agent1>
          [Но|А] there should not be agent <agent2>

          Примеры:
            | agent1 | agent2 |
            | D      | M      |
      """

  Scenario: Print available definitions
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Exception\PendingException;

      class FeatureContext implements Context
      {
          /**
           * @Given /^(?:I|We) have (\d+) apples?$/
           */
          public function iHaveApples($count) {
              throw new PendingException();
          }

          /**
           * @When /^(?:I|We) ate (\d+) apples?$/
           */
          public function iAteApples($count) {
              throw new PendingException();
          }

          /**
           * @When /^(?:I|We) found (\d+) apples?$/
           */
          public function iFoundApples($count) {
              throw new PendingException();
          }

          /**
           * @Then /^(?:I|We) should have (\d+) apples$/
           */
          public function iShouldHaveApples($count) {
              throw new PendingException();
          }
      }
      """
    When I run "behat --no-colors -dl"
    Then the output should contain:
      """
      default | Given /^(?:I|We) have (\d+) apples?$/
      default |  When /^(?:I|We) ate (\d+) apples?$/
      default |  When /^(?:I|We) found (\d+) apples?$/
      default |  Then /^(?:I|We) should have (\d+) apples$/
      """

  Scenario: Print available definitions in native language
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Exception\PendingException,
          Behat\Behat\Context\TranslatableContext;

      class FeatureContext implements TranslatableContext
      {
          /**
           * @Given /^I have (\d+) apples?$/
           */
          public function iHaveApples($count) {
              throw new PendingException();
          }

          /**
           * @When /^I ate (\d+) apples?$/
           */
          public function iAteApples($count) {
              throw new PendingException();
          }

          /**
           * @When /^I found (\d+) apples?$/
           */
          public function iFoundApples($count) {
              throw new PendingException();
          }

          /**
           * @Then /^I should have (\d+) apples$/
           */
          public function iShouldHaveApples($count) {
              throw new PendingException();
          }

          public static function getTranslationResources() {
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
            <trans-unit id="i-have-apples">
              <source>/^I have (\d+) apples?$/</source>
              <target>/^у меня (\d+) яблоко?$/</target>
            </trans-unit>
            <trans-unit id="i-found">
              <source>/^I found (\d+) apples?$/</source>
              <target>/^Я нашел (\d+) яблоко?$/</target>
            </trans-unit>
          </body>
        </file>
      </xliff>
      """
    When I run "behat --no-colors -dl --lang=ru"
    Then the output should contain:
      """
      default | Given /^у меня (\d+) яблоко?$/
      default |  When /^I ate (\d+) apples?$/
      default |  When /^Я нашел (\d+) яблоко?$/
      default |  Then /^I should have (\d+) apples$/
      """

  Scenario: Print extended definitions info
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Exception\PendingException;

      class FeatureContext implements Context
      {
          /**
           * @Given /^I have (\d+) apples?$/
           */
          public function iHaveApples($count) {
              throw new PendingException();
          }

          /**
           * Eating apples
           * 
           * More details on eating apples, and a list:
           * - one
           * - two
           * --
           * Internal note not showing in help
           *
           * @When /^I ate (\d+) apples?$/
           */
          public function iAteApples($count) {
              throw new PendingException();
          }

          /**
           * @When /^I found (\d+) apples?$/
           */
          public function iFoundApples($count) {
              throw new PendingException();
          }

          /**
           * @Then /^I should have (\d+) apples$/
           */
          public function iShouldHaveApples($count) {
              throw new PendingException();
          }
      }
      """
    When I run "behat --no-colors -di"
    Then the output should contain:
      """
      default | Given /^I have (\d+) apples?$/
              | at `FeatureContext::iHaveApples()`

      default | When /^I ate (\d+) apples?$/
              | Eating apples
              |
              | More details on eating apples, and a list:
              | - one
              | - two
              | at `FeatureContext::iAteApples()`

      default | When /^I found (\d+) apples?$/
              | at `FeatureContext::iFoundApples()`

      default | Then /^I should have (\d+) apples$/
              | at `FeatureContext::iShouldHaveApples()`
      """

  Scenario: Search definition
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Exception\PendingException,
          Behat\Behat\Context\TranslatableContext;

      class FeatureContext implements TranslatableContext
      {
          /**
           * @Given /^I have (\d+) apples?$/
           */
          public function iHaveApples($count) {
              throw new PendingException();
          }

          /**
           * @When /^I ate (\d+) apples?$/
           */
          public function iAteApples($count) {
              throw new PendingException();
          }

          /**
           * @When /^I found (\d+) apples?$/
           */
          public function iFoundApples($count) {
              throw new PendingException();
          }

          /**
           * @Then /^I should have (\d+) apples$/
           */
          public function iShouldHaveApples($count) {
              throw new PendingException();
          }

          public static function getTranslationResources() {
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
            <trans-unit id="i-have-apples">
              <source>/^I have (\d+) apples?$/</source>
              <target>/^у меня (\d+) яблоко?$/</target>
            </trans-unit>
            <trans-unit id="i-found">
              <source>/^I found (\d+) apples?$/</source>
              <target>/^Я нашел (\d+) яблоко?$/</target>
            </trans-unit>
          </body>
        </file>
      </xliff>
      """
    When I run "behat --no-colors --lang=ru -d 'нашел'"
    Then the output should contain:
      """
      default | When /^Я нашел (\d+) яблоко?$/
              | at `FeatureContext::iFoundApples()`
      """
