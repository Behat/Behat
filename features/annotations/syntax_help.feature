Feature: Syntax helpers
  In order to get syntax help
  As a feature writer
  I need to be able to print supported definitions and Gherkin keywords

  Scenario: Print story syntax
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php class FeatureContext extends Behat\Behat\Context\BehatContext {}
      """
    When I run "behat --no-ansi --story-syntax"
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
      <?php class FeatureContext extends Behat\Behat\Context\BehatContext {}
      """
    When I run "behat --no-ansi --story-syntax --lang ru"
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

      use Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException;

      class FeatureContext extends BehatContext
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
    When I run "behat --no-ansi -dl"
    Then the output should contain:
      """
      Given /^(?:I|We) have (\d+) apples?$/
       When /^(?:I|We) ate (\d+) apples?$/
       When /^(?:I|We) found (\d+) apples?$/
       Then /^(?:I|We) should have (\d+) apples$/
      """

  Scenario: Print available definitions (ansi)
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException;

      class FeatureContext extends BehatContext
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
    When I run "behat -dl --ansi"
    And I escape ansi characters in the output
    Then the output should contain:
      """
      \033[32mGiven\033[0m \033[33m/^(?:I|We) have \033[0m\033[33;1m(\d+)\033[0m\033[33m apples?$/\033[0m
      \033[32m When\033[0m \033[33m/^(?:I|We) ate \033[0m\033[33;1m(\d+)\033[0m\033[33m apples?$/\033[0m
      \033[32m When\033[0m \033[33m/^(?:I|We) found \033[0m\033[33;1m(\d+)\033[0m\033[33m apples?$/\033[0m
      \033[32m Then\033[0m \033[33m/^(?:I|We) should have \033[0m\033[33;1m(\d+)\033[0m\033[33m apples$/\033[0m
      """

  Scenario: Print available definitions in native language
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException,
          Behat\Behat\Context\TranslatedContextInterface;

      class FeatureContext extends BehatContext implements TranslatedContextInterface
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
    When I run "behat --no-ansi -dl --lang=ru"
    Then the output should contain:
      """
      Given /^у меня (\d+) яблоко?$/
       When /^I ate (\d+) apples?$/
       When /^Я нашел (\d+) яблоко?$/
       Then /^I should have (\d+) apples$/
      """

  Scenario: Print extended definitions info
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException;

      class FeatureContext extends BehatContext
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
    When I run "behat --no-ansi -di"
    Then the output should contain:
      """
      Given /^I have (\d+) apples?$/
          # FeatureContext::iHaveApples()

       When /^I ate (\d+) apples?$/
          - Eating apples
          # FeatureContext::iAteApples()

       When /^I found (\d+) apples?$/
          # FeatureContext::iFoundApples()

       Then /^I should have (\d+) apples$/
          # FeatureContext::iShouldHaveApples()
      """

  Scenario: Search definition
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException;

      class FeatureContext extends BehatContext
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
    When I run "behat --no-ansi -d 'found apples'"
    Then the output should contain:
      """
      When /^I found (\d+) apples?$/
          # FeatureContext::iFoundApples()
      """

  Scenario: Search definition
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException,
          Behat\Behat\Context\TranslatedContextInterface;

      class FeatureContext extends BehatContext implements TranslatedContextInterface
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
    When I run "behat --no-ansi --lang=ru -d 'нашел'"
    Then the output should contain:
      """
      When /^Я нашел (\d+) яблоко?$/
          # FeatureContext::iFoundApples()
      """
