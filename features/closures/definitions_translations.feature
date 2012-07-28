Feature: Definitions translations
  In order to be able to use predefined steps in native language
  As a step definitions developer
  I need to be able to write definition translations

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\ClosuredContextInterface as Closured,
          Behat\Behat\Context\TranslatedContextInterface as Translated,
          Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;
      use Symfony\Component\Finder\Finder;

      if (file_exists(__DIR__ . '/../support/bootstrap.php')) {
          require_once __DIR__ . '/../support/bootstrap.php';
      }

      class FeatureContext extends BehatContext implements Closured, Translated
      {
          public $parameters = array();

          public function __construct(array $parameters) {
              $this->parameters = $parameters;

              if (file_exists(__DIR__ . '/../support/env.php')) {
                  $world = $this;
                  require(__DIR__ . '/../support/env.php');
              }
          }

          public function getStepDefinitionResources() {
              if (file_exists(__DIR__ . '/../steps')) {
                  $finder = new Finder();
                  return $finder->files()->name('*.php')->in(__DIR__ . '/../steps');
              }
              return array();
          }

          public function getHookDefinitionResources() {
              if (file_exists(__DIR__ . '/../support/hooks.php')) {
                  return array(__DIR__ . '/../support/hooks.php');
              }
              return array();
          }

          public function getTranslationResources() {
              if (file_exists(__DIR__ . '/../steps/i18n')) {
                  $finder = new Finder();
                  return $finder->files()->name('*.xliff')->in(__DIR__ . '/../steps/i18n');
              }
              return array();
          }

          public function __call($name, array $args) {
              if (isset($this->$name) && is_callable($this->$name)) {
                  return call_user_func_array($this->$name, $args);
              } else {
                  $trace = debug_backtrace();
                  trigger_error(
                      'Call to undefined method ' . get_class($this) . '::' . $name .
                      ' in ' . $trace[0]['file'] .
                      ' on line ' . $trace[0]['line'],
                      E_USER_ERROR
                  );
              }
          }
      }
      """
    And a file named "features/support/bootstrap.php" with:
      """
      <?php
      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';
      """

  Scenario: In place translations
    Given a file named "features/calc_ru.feature" with:
      """
      # language: ru
      Функция: Базовая калькуляция

        Сценарий:
          Допустим Я набрал число 10 на калькуляторе
          И Я набрал число 4 на калькуляторе
          И Я нажал "+"
          То Я должен увидеть на экране 14
      """
    And a file named "features/steps/calc_steps.php" with:
      """
      <?php
      $steps->Given('/^I have entered (\d+) into calculator$/', function($world, $number) {
          $world->numbers[] = intval($number);
      });
      $steps->Given('/^I have clicked "+"$/', function($world) {
          $world->result = array_sum($world->numbers);
      });
      $steps->Then('/^I should see (\d+) on the screen$/', function($world, $result) {
          assertEquals(intval($result), $world->result);
      });
      """
    And a file named "features/steps/i18n/ru.xliff" with:
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
          </body>
        </file>
      </xliff>
      """
    When I run "behat --no-ansi -f progress features/calc_ru.feature"
    Then it should pass with:
      """
      ....

      1 scenario (1 passed)
      4 steps (4 passed)
      """
