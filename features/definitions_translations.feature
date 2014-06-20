Feature: Definitions translations
  In order to be able to use predefined steps in native language
  As a step definitions developer
  I need to be able to write definition translations

  Scenario: In place XLIFF translations
    Given a file named "features/calc_ru.feature" with:
      """
      # language: ru
      Функция: Базовая калькуляция

        Сценарий:
          Допустим Я набрал число 10 на калькуляторе
          И Я набрал число 4 на калькуляторе
          И Я нажал "+"
          То Я должен увидеть на экране 14
          И пользователь "everzet" должен иметь имя "everzet"
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\TranslatableContext;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements TranslatableContext
      {
          private $numbers = array();
          private $result = 0;

          /**
           * @Given /^I have entered (\d+) into calculator$/
           */
          public function iHaveEnteredIntoCalculator($number) {
              $this->numbers[] = intval($number);
          }

          /**
           * @Given /^I have clicked "+"$/
           */
          public function iHaveClickedPlus() {
              $this->result = array_sum($this->numbers);
          }

          /**
           * @Then /^I should see (\d+) on the screen$/
           */
          public function iShouldSeeOnTheScreen($result) {
              PHPUnit_Framework_Assert::assertEquals(intval($result), $this->result);
          }

          /** @Transform /"([^"]+)" user/ */
          public static function createUserFromUsername($username) {
              return (Object) array('name' => $username);
          }

          /**
           * @Then /^the ("[^"]+" user) name should be "([^"]*)"$/
           */
          public function theUserUsername($user, $username) {
              PHPUnit_Framework_Assert::assertEquals($username, $user->name);
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
            <trans-unit id="i-have-entered">
              <source>/^I have entered (\d+) into calculator$/</source>
              <target>/^Я набрал число (\d+) на калькуляторе$/</target>
            </trans-unit>
            <trans-unit id="i-have-clicked-plus">
              <source>/^I have clicked "+"$/</source>
              <target>/^Я нажал "([^"]*)"$/</target>
            </trans-unit>
            <trans-unit id="i-should-see">
              <source>/^I should see (\d+) on the screen$/</source>
              <target>/^Я должен увидеть на экране (\d+)$/</target>
            </trans-unit>
            <trans-unit id="the-user">
              <source>/"([^"]+)" user/</source>
              <target>/пользователь "([^"]+)"/</target>
            </trans-unit>
            <trans-unit id="the-user-name-should-be">
              <source>/^the ("[^"]+" user) name should be "([^"]*)"$/</source>
              <target>/^(пользователь "[^"]+") должен иметь имя "([^"]*)"$/</target>
            </trans-unit>
          </body>
        </file>
      </xliff>
      """
    When I run "behat --no-colors -f progress features/calc_ru.feature"
    Then it should pass with:
      """
      .....

      1 scenario (1 passed)
      5 steps (5 passed)
      """

  Scenario: In place YAML translations
    Given a file named "features/calc_ru.feature" with:
      """
      # language: ru
      Функция: Базовая калькуляция

        Сценарий:
          Допустим Я набрал число 10 на калькуляторе
          И Я набрал число 4 на калькуляторе
          И Я нажал "+"
          То Я должен увидеть на экране 14
          И пользователь "everzet" должен иметь имя "everzet"
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\TranslatableContext;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements TranslatableContext
      {
          private $numbers = array();
          private $result = 0;

          /**
           * @Given /^I have entered (\d+) into calculator$/
           */
          public function iHaveEnteredIntoCalculator($number) {
              $this->numbers[] = intval($number);
          }

          /**
           * @Given /^I have clicked "+"$/
           */
          public function iHaveClickedPlus() {
              $this->result = array_sum($this->numbers);
          }

          /**
           * @Then /^I should see (\d+) on the screen$/
           */
          public function iShouldSeeOnTheScreen($result) {
              PHPUnit_Framework_Assert::assertEquals(intval($result), $this->result);
          }

          /** @Transform /"([^"]+)" user/ */
          public static function createUserFromUsername($username) {
              return (Object) array('name' => $username);
          }

          /**
           * @Then /^the ("[^"]+" user) name should be "([^"]*)"$/
           */
          public function theUserUsername($user, $username) {
              PHPUnit_Framework_Assert::assertEquals($username, $user->name);
          }

          public static function getTranslationResources() {
              return array(__DIR__ . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'ru.yml');
          }
      }
      """
    And a file named "features/bootstrap/i18n/ru.yml" with:
      """
      '/^I have entered (\d+) into calculator$/':         '/^Я набрал число (\d+) на калькуляторе$/'
      '/^I have clicked "+"$/':                           '/^Я нажал "([^"]*)"$/'
      '/^I should see (\d+) on the screen$/':             '/^Я должен увидеть на экране (\d+)$/'
      '/"([^"]+)" user/':                                 '/пользователь "([^"]+)"/'
      '/^the ("[^"]+" user) name should be "([^"]*)"$/':  '/^(пользователь "[^"]+") должен иметь имя "([^"]*)"$/'
      """
    When I run "behat --no-colors -f progress features/calc_ru.feature"
    Then it should pass with:
      """
      .....

      1 scenario (1 passed)
      5 steps (5 passed)
      """

  Scenario: In place PHP translations
    Given a file named "features/calc_ru.feature" with:
      """
      # language: ru
      Функция: Базовая калькуляция

        Сценарий:
          Допустим Я набрал число 10 на калькуляторе
          И Я набрал число 4 на калькуляторе
          И Я нажал "+"
          То Я должен увидеть на экране 14
          И пользователь "everzet" должен иметь имя "everzet"
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\TranslatableContext;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements TranslatableContext
      {
          private $numbers = array();
          private $result = 0;

          /**
           * @Given /^I have entered (\d+) into calculator$/
           */
          public function iHaveEnteredIntoCalculator($number) {
              $this->numbers[] = intval($number);
          }

          /**
           * @Given /^I have clicked "+"$/
           */
          public function iHaveClickedPlus() {
              $this->result = array_sum($this->numbers);
          }

          /**
           * @Then /^I should see (\d+) on the screen$/
           */
          public function iShouldSeeOnTheScreen($result) {
              PHPUnit_Framework_Assert::assertEquals(intval($result), $this->result);
          }

          /** @Transform /"([^"]+)" user/ */
          public static function createUserFromUsername($username) {
              return (Object) array('name' => $username);
          }

          /**
           * @Then /^the ("[^"]+" user) name should be "([^"]*)"$/
           */
          public function theUserUsername($user, $username) {
              PHPUnit_Framework_Assert::assertEquals($username, $user->name);
          }

          public static function getTranslationResources() {
              return array(__DIR__ . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'ru.php');
          }
      }
      """
    And a file named "features/bootstrap/i18n/ru.php" with:
      """
      <?php return array(
        '/^I have entered (\d+) into calculator$/'        => '/^Я набрал число (\d+) на калькуляторе$/',
        '/^I have clicked "+"$/'                          => '/^Я нажал "([^"]*)"$/',
        '/^I should see (\d+) on the screen$/'            => '/^Я должен увидеть на экране (\d+)$/',
        '/"([^"]+)" user/'                                => '/пользователь "([^"]+)"/',
        '/^the ("[^"]+" user) name should be "([^"]*)"$/' => '/^(пользователь "[^"]+") должен иметь имя "([^"]*)"$/',
      );
      """
    When I run "behat --no-colors -f progress features/calc_ru.feature"
    Then it should pass with:
      """
      .....

      1 scenario (1 passed)
      5 steps (5 passed)
      """

  Scenario: Translations with 2 suites
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          frontend: ~
          backend: ~
      """
    Given a file named "features/calc_ru.feature" with:
      """
      # language: ru
      Функция: Базовая калькуляция

        Сценарий:
          Допустим Я набрал число 10 на калькуляторе
          И Я набрал число 4 на калькуляторе
          И Я нажал "+"
          То Я должен увидеть на экране 14
          И пользователь "everzet" должен иметь имя "everzet"
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\TranslatableContext;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements TranslatableContext
      {
          private $numbers = array();
          private $result = 0;

          /**
           * @Given /^I have entered (\d+) into calculator$/
           */
          public function iHaveEnteredIntoCalculator($number) {
              $this->numbers[] = intval($number);
          }

          /**
           * @Given /^I have clicked "+"$/
           */
          public function iHaveClickedPlus() {
              $this->result = array_sum($this->numbers);
          }

          /**
           * @Then /^I should see (\d+) on the screen$/
           */
          public function iShouldSeeOnTheScreen($result) {
              PHPUnit_Framework_Assert::assertEquals(intval($result), $this->result);
          }

          /** @Transform /"([^"]+)" user/ */
          public static function createUserFromUsername($username) {
              return (Object) array('name' => $username);
          }

          /**
           * @Then /^the ("[^"]+" user) name should be "([^"]*)"$/
           */
          public function theUserUsername($user, $username) {
              PHPUnit_Framework_Assert::assertEquals($username, $user->name);
          }

          public static function getTranslationResources() {
              return array(__DIR__ . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'ru.php');
          }
      }
      """
    And a file named "features/bootstrap/i18n/ru.php" with:
      """
      <?php return array(
        '/^I have entered (\d+) into calculator$/'        => '/^Я набрал число (\d+) на калькуляторе$/',
        '/^I have clicked "+"$/'                          => '/^Я нажал "([^"]*)"$/',
        '/^I should see (\d+) on the screen$/'            => '/^Я должен увидеть на экране (\d+)$/',
        '/"([^"]+)" user/'                                => '/пользователь "([^"]+)"/',
        '/^the ("[^"]+" user) name should be "([^"]*)"$/' => '/^(пользователь "[^"]+") должен иметь имя "([^"]*)"$/',
      );
      """
    When I run "behat --no-colors -f progress features/calc_ru.feature"
    Then it should pass with:
      """
      ..........

      2 scenarios (2 passed)
      10 steps (10 passed)
      """
