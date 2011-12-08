Feature: Syntax helpers
  In order to get syntax help
  As a feature writer
  I need to be able to print supported definitions and Gherkin keywords

  Scenario: Print story syntax
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php class FeatureContext extends Behat\Behat\Context\BehatContext {}
      """
    When I run "behat --story-syntax"
    Then the output should contain:
      """
      # language: en
      Feature: feature title
        In order to ...
        As a ...
        I need to ...

        Background:
          [Given, Then, When, But, And] step 1
          [Given, Then, When, But, And] step 2

        Scenario: scenario title
          [Given, Then, When, But, And] step 1
          [Given, Then, When, But, And] step 2

        [Scenario Outline, Scenario Template]: outline title
          [Given, Then, When, But, And] step <val1>
          [Given, Then, When, But, And] step <val2>

          [Examples, Scenarios]:
            | val1 | val2 |
            | 23   | 122  |
      """

  Scenario: Print story syntax in native language
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php class FeatureContext extends Behat\Behat\Context\BehatContext {}
      """
    When I run "behat --story-syntax --lang ru"
    Then the output should contain:
      """
      # language: ru
      [Функционал, Фича]: feature title
        In order to ...
        As a ...
        I need to ...

        Предыстория:
          [К тому же, Допустим, Когда, Пусть, Тогда, Если, Дано, Но, То, А, И] step 1
          [К тому же, Допустим, Когда, Пусть, Тогда, Если, Дано, Но, То, А, И] step 2

        Сценарий: scenario title
          [К тому же, Допустим, Когда, Пусть, Тогда, Если, Дано, Но, То, А, И] step 1
          [К тому же, Допустим, Когда, Пусть, Тогда, Если, Дано, Но, То, А, И] step 2

        Структура сценария: outline title
          [К тому же, Допустим, Когда, Пусть, Тогда, Если, Дано, Но, То, А, И] step <val1>
          [К тому же, Допустим, Когда, Пусть, Тогда, Если, Дано, Но, То, А, И] step <val2>

          Значения:
            | val1 | val2 |
            | 23   | 122  |
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
      }
      """
    When I run "behat --definitions"
    Then the output should contain:
      """
      Given /^I have (\d+) apples?$/
       When /^I ate (\d+) apples?$/
       When /^I found (\d+) apples?$/
       Then /^I should have (\d+) apples$/
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
    When I run "behat -d --lang=ru"
    Then the output should contain:
      """
      Given /^у меня (\d+) яблоко?$/
       When /^I ate (\d+) apples?$/
       When /^Я нашел (\d+) яблоко?$/
       Then /^I should have (\d+) apples$/
      """

  Scenario: Print available definitions with functions associated
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
    When I run "behat --definitions-source"
    Then the output should contain:
      """
      Given /^I have (\d+) apples?$/       # FeatureContext::iHaveApples()
       When /^I ate (\d+) apples?$/        # FeatureContext::iAteApples()
       When /^I found (\d+) apples?$/      # FeatureContext::iFoundApples()
       Then /^I should have (\d+) apples$/ # FeatureContext::iShouldHaveApples()
      """
