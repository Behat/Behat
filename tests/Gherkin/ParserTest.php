<?php

use \Everzet\Gherkin\I18n;
use \Everzet\Gherkin\Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    private function loadFeature($path)
    {
        $i18n = new I18n(__DIR__ . '/../../i18n');
        $parser = new Parser($i18n);
        return $parser->parse(file_get_contents(__DIR__ . '/fixtures/features/' . $path));
    }

    private function loadFeatureFromFile($path)
    {
        $i18n = new I18n(__DIR__ . '/../../i18n');
        $parser = new Parser($i18n);
        return $parser->parseFile(__DIR__ . '/fixtures/features/' . $path);
    }

    public function testDosLineEndingsFeature()
    {
        $feature = $this->loadFeature('dos_line_endings.feature');

        $this->assertEquals('DOS line endings', $feature->getTitle());
        $this->assertTrue($feature->hasDescription());
        $this->assertFalse($feature->hasBackground());
        $this->assertEquals('I want to write features with DOS line endigs', 
            end($feature->getDescription()));
        $this->assertTrue($feature->hasScenarios());

        $scenario = end($feature->getScenarios());
        $this->assertEquals('Just lots of DOS', $scenario->getTitle());
        $this->assertTrue($scenario->hasSteps());

        $steps = $scenario->getSteps();
        $this->assertEquals('Given', $steps[0]->getType());
        $this->assertEquals('I am on DOS', $steps[0]->getText());
        $this->assertEquals('And', $steps[1]->getType());
        $this->assertEquals('Any version of Windows is really just DOS', $steps[1]->getText());
        $this->assertEquals('Then', $steps[2]->getType());
        $this->assertEquals('line endings are CRLF', $steps[2]->getText());
    }

    public function testBackgroundsFeature()
    {
        $feature = $this->loadFeature('background.feature');

        $this->assertEquals('Feature with background', $feature->getTitle());
        $this->assertFalse($feature->hasDescription());
        $this->assertTrue($feature->hasBackground());

        $background = $feature->getBackground();
        $this->assertTrue($background->hasSteps());
        $this->assertEquals('Given', end($background->getSteps())->getType());
        $this->assertEquals('a passing step', end($background->getSteps())->getText());

        $scenario = end($feature->getScenarios());
        $this->assertEquals('', $scenario->getTitle());
        $this->assertTrue($scenario->hasSteps());
        $this->assertEquals('Given', end($scenario->getSteps())->getType());
        $this->assertEquals('a failing step', end($scenario->getSteps())->getText());
    }

    public function testTagsSample()
    {
        $feature = $this->loadFeature('tags_sample.feature');

        $this->assertEquals('Tag samples', $feature->getTitle());
        $this->assertEquals(array('sample_one'), $feature->getTags());
        $this->assertTrue($feature->hasTag('sample_one'));
        $this->assertTrue($feature->hasScenarios());

        $scenarios = $feature->getScenarios();

        $this->assertEquals('Passing', $scenarios[0]->getTitle());
        $this->assertTrue($scenarios[0]->hasTags());
        $this->assertEquals(array('sample_two', 'sample_four'), $scenarios[0]->getTags());
        $this->assertFalse($scenarios[0]->hasTag('sample_one'));
        $this->assertTrue($scenarios[0]->hasTag('sample_two'));
        $this->assertFalse($scenarios[0]->hasTag('sample_three'));
        $this->assertTrue($scenarios[0]->hasTag('sample_four'));
        $this->assertTrue($scenarios[0]->hasSteps());
        $this->assertEquals(1, count($scenarios[0]->getSteps()));
        $this->assertEquals('Given', end($scenarios[0]->getSteps())->getType());
        $this->assertEquals('missing', end($scenarios[0]->getSteps())->getText());

        $this->assertEquals('', $scenarios[1]->getTitle());
        $this->assertTrue($scenarios[1]->hasTags());
        $this->assertEquals(array('sample_three'), $scenarios[1]->getTags());
        $this->assertFalse($scenarios[1]->hasTag('sample_one'));
        $this->assertFalse($scenarios[1]->hasTag('sample_two'));
        $this->assertTrue($scenarios[1]->hasTag('sample_three'));
        $this->assertFalse($scenarios[1]->hasTag('sample_four'));
        $this->assertTrue($scenarios[1]->hasSteps());
        $this->assertEquals(1, count($scenarios[1]->getSteps()));
        $this->assertEquals('Given', end($scenarios[1]->getSteps())->getType());
        $this->assertEquals('<state>', end($scenarios[1]->getSteps())->getText());
        $this->assertTrue($scenarios[1]->hasExamples());
        $this->assertEquals(
            array(array('state' => 'missing')),
            $scenarios[1]->getExamples()->getTable()->getHash()
        );

        $this->assertEquals('Skipped', $scenarios[2]->getTitle());
        $this->assertTrue($scenarios[2]->hasTags());
        $this->assertEquals(array('sample_three', 'sample_four'), $scenarios[2]->getTags());
        $this->assertFalse($scenarios[2]->hasTag('sample_one'));
        $this->assertFalse($scenarios[2]->hasTag('sample_two'));
        $this->assertTrue($scenarios[2]->hasTag('sample_three'));
        $this->assertTrue($scenarios[2]->hasTag('sample_four'));
        $this->assertTrue($scenarios[2]->hasSteps());
    }

    public function testTables()
    {
        $feature = $this->loadFeature('tables.feature');

        $this->assertEquals('A scenario outline', $feature->getTitle());
        $this->assertTrue($feature->hasScenarios());
        $this->assertEquals(1, count($feature->getScenarios()));

        $outline = end($feature->getScenarios());

        $this->assertEquals('', $outline->getTitle());
        $this->assertTrue($outline->hasSteps());
        $this->assertEquals(3, count($outline->getSteps()));

        $steps = $outline->getSteps();

        $this->assertEquals('Given', $steps[0]->getType());
        $this->assertEquals('When', $steps[1]->getType());
        $this->assertEquals('Then', $steps[2]->getType());
        $this->assertEquals('I add <a> and <b>', $steps[0]->getText());
        $this->assertEquals('I pass a table argument', $steps[1]->getText());
        $this->assertEquals('I the result should be <c>', $steps[2]->getText());

        $this->assertEquals(
            array(array('foo' => 'bar', 'bar' => 'baz')),
            end($steps[1]->getArguments())->getHash()
        );

        $this->assertTrue($outline->hasExamples());
        $this->assertEquals(
            array(
                array('a' => '1', 'b' => '2', 'c' => '3'),
                array('a' => '2', 'b' => '3', 'c' => '4'),
            ),
            $outline->getExamples()->getTable()->getHash()
        );
    }

    public function testPyString()
    {
        $feature = $this->loadFeature('pystring.feature');

        $this->assertEquals('A py string feature', $feature->getTitle());
        $this->assertTrue($feature->hasScenarios());
        $this->assertEquals(1, count($feature->getScenarios()));

        $scenario = end($feature->getScenarios());

        $this->assertEquals('', $scenario->getTitle());
        $this->assertTrue($scenario->hasSteps());
        $this->assertEquals(1, count($scenario->getSteps()));

        $step = end($scenario->getSteps());

        $this->assertEquals('Then', $step->getType());
        $this->assertEquals('I should see', $step->getText());
        $this->assertEquals('a string', (string) end($step->getArguments()));
    }

    public function testUndefinedMultilineArgs()
    {
        $feature = $this->loadFeature('undefined_multiline_args.feature');

        $this->assertEquals('undefined multiline args', $feature->getTitle());
        $this->assertFalse($feature->hasDescription());
        $this->assertTrue($feature->hasScenarios());
        $this->assertEquals(2, count($feature->getScenarios()));

        $scenarios = $feature->getScenarios();

        $this->assertEquals('pystring', $scenarios[0]->getTitle());
        $this->assertEquals(1, count($scenarios[0]->getSteps()));

        $step = end($scenarios[0]->getSteps());
        $this->assertEquals('Given', $step->getType());
        $this->assertEquals('a pystring', $step->getText());
        $this->assertEquals('  example', (string) end($step->getArguments()));

        $this->assertEquals('table', $scenarios[1]->getTitle());
        $this->assertEquals(1, count($scenarios[0]->getSteps()));

        $step = end($scenarios[1]->getSteps());
        $this->assertEquals('Given', $step->getType());
        $this->assertEquals('a table', $step->getText());
        $this->assertEquals(
            array(array('table' => 'example')),
            end($step->getArguments())->getHash()
        );
    }

    public function testUnit()
    {
        $feature = $this->loadFeature('test_unit.feature');

        $this->assertEquals('Test::Unit', $feature->getTitle());
        $this->assertTrue($feature->hasDescription());
        $this->assertEquals(
            array(
                'In order to please people who like Test::Unit',
                'As a Cucumber user',
                'I want to be able to use assert* in my step definitions'
            ),
            $feature->getDescription()
        );

        $this->assertTrue($feature->hasScenarios());
        $this->assertEquals(1, count($feature->getScenarios()));

        $scenario = end($feature->getScenarios());

        $this->assertEquals('assert_equal', $scenario->getTitle());
        $this->assertEquals(3, count($scenario->getSteps()));

        $steps = $scenario->getSteps();

        $this->assertEquals('Given', $steps[0]->getType());
        $this->assertEquals('And', $steps[1]->getType());
        $this->assertEquals('Then', $steps[2]->getType());
        $this->assertEquals('x = 5', $steps[0]->getText());
        $this->assertEquals('y = 5', $steps[1]->getText());
        $this->assertEquals('I can assert that x == y', $steps[2]->getText());
    }

    public function testFibonacci()
    {
        $feature = $this->loadFeature('fibonacci.feature');

        $this->assertEquals('Fibonacci', $feature->getTitle());
        $this->assertTrue($feature->hasDescription());
        $this->assertEquals(
            array(
                'In order to calculate super fast fibonacci series',
                'As a pythonista',
                'I want to use Python for that'
            ),
            $feature->getDescription()
        );
        $this->assertTrue($feature->hasScenarios());
        $this->assertEquals(1, count($feature->getScenarios()));

        $outline = end($feature->getScenarios());

        $this->assertEquals('Series', $outline->getTitle());
        $this->assertEquals(2, count($outline->getSteps()));

        $steps = $outline->getSteps();
        $this->assertEquals('When', $steps[0]->getType());
        $this->assertEquals('Then', $steps[1]->getType());
        $this->assertEquals('I ask python to calculate fibonacci up to <n>', $steps[0]->getText());
        $this->assertEquals('it should give me <series>', $steps[1]->getText());

        $this->assertEquals(
            array(
                array('n' => 1, 'series' => '[]'),
                array('n' => 2, 'series' => '[1, 1]'),
                array('n' => 3, 'series' => '[1, 1, 2]'),
                array('n' => 4, 'series' => '[1, 1, 2, 3]'),
                array('n' => 6, 'series' => '[1, 1, 2, 3, 5]'),
                array('n' => 9, 'series' => '[1, 1, 2, 3, 5, 8]'),
                array('n' => 100, 'series' => '[1, 1, 2, 3, 5, 8, 13, 21, 34, 55, 89]'),
            ),
            $outline->getExamples()->getTable()->getHash()
        );
    }

    public function testMultilineName()
    {
        $feature = $this->loadFeature('multiline_name.feature');

        $this->assertEquals('multiline', $feature->getTitle());
        $this->assertFalse($feature->hasDescription());
        $this->assertTrue($feature->hasBackground());
        $this->assertNotEquals(null, $feature->getBackground());

        $item = $feature->getBackground();

        $this->assertEquals(
            "I'm a multiline name which goes on and on and on for three lines yawn", 
            $item->getTitle()
        );

        $this->assertTrue($item->hasSteps());
        $this->assertEquals(1, count($item->getSteps()));
        $this->assertEquals('Given', end($item->getSteps())->getType());
        $this->assertEquals('passing without a table', end($item->getSteps())->getText());

        $scenarios = $feature->getScenarios();

        $item = $scenarios[0];
        $this->assertEquals(
            "I'm a multiline name which goes on and on and on for three lines yawn", 
            $item->getTitle()
        );

        $this->assertTrue($item->hasSteps());
        $this->assertEquals(1, count($item->getSteps()));
        $this->assertEquals('Given', end($item->getSteps())->getType());
        $this->assertEquals('passing without a table', end($item->getSteps())->getText());

        $item = $scenarios[1];
        $this->assertEquals(
            "I'm a multiline name which goes on and on and on for three lines yawn", 
            $item->getTitle()
        );

        $this->assertTrue($item->hasSteps());
        $this->assertEquals(1, count($item->getSteps()));
        $this->assertEquals('Given', end($item->getSteps())->getType());
        $this->assertEquals('<state> without a table', end($item->getSteps())->getText());
        $this->assertEquals(
            array(array('state' => 'passing')),
            $item->getExamples()->getTable()->getHash()
        );

        $item = $scenarios[2];
        $this->assertEquals('name', $item->getTitle());

        $this->assertTrue($item->hasSteps());
        $this->assertEquals(1, count($item->getSteps()));
        $this->assertEquals('Given', end($item->getSteps())->getType());
        $this->assertEquals('<state> without a table', end($item->getSteps())->getText());
        $this->assertEquals(
            array(array('state' => 'passing')),
            $item->getExamples()->getTable()->getHash()
        );
    }

    public function test172()
    {
        $feature = $this->loadFeature('172.feature');

        $this->assertEquals('Login', $feature->getTitle());
        $this->assertTrue($feature->hasDescription());
        $this->assertEquals(
            array(
                'To ensure the safety of the application',
                'A regular user of the system',
                'Must authenticate before using the app'
            ),
            $feature->getDescription()
        );

        $this->assertTrue($feature->hasScenarios());
        $this->assertEquals(1, count($feature->getScenarios()));

        $scenario = end($feature->getScenarios());

        $this->assertEquals('Failed Login', $scenario->getTitle());
        $this->assertTrue($scenario->hasExamples());
        $this->assertEquals(
            array(
                array('login' => '', 'password' => ''),
                array('login' => 'unknown_user', 'password' => ''),
                array('login' => 'known_user', 'password' => ''),
                array('login' => '', 'password' => 'wrong_password'),
                array('login' => '', 'password' => 'known_userpass'),
                array('login' => 'unknown_user', 'password' => 'wrong_password'),
                array('login' => 'unknown_user', 'password' => 'known_userpass'),
                array('login' => 'known_user', 'password' => 'wrong_password'),
            ),
            $scenario->getExamples()->getTable()->getHash()
        );

        $this->assertTrue($scenario->hasSteps());
        $steps = $scenario->getSteps();
        $this->assertEquals(8, count($steps));

        $this->assertEquals('Given', $steps[0]->getType());
        $this->assertEquals('the user "known_user"', $steps[0]->getText());

        $this->assertEquals('When', $steps[1]->getType());
        $this->assertEquals('I go to the main page', $steps[1]->getText());

        $this->assertEquals('Then', $steps[2]->getType());
        $this->assertEquals('I should see the login form', $steps[2]->getText());

        $this->assertEquals('When', $steps[3]->getType());
        $this->assertEquals('I fill in "login" with "<login>"', $steps[3]->getText());

        $this->assertEquals('And', $steps[4]->getType());
        $this->assertEquals('I fill in "password" with "<password>"', $steps[4]->getText());

        $this->assertEquals('And', $steps[5]->getType());
        $this->assertEquals('I press "Log In"', $steps[5]->getText());

        $this->assertEquals('Then', $steps[6]->getType());
        $this->assertEquals('the login request should fail', $steps[6]->getText());

        $this->assertEquals('And', $steps[7]->getType());
        $this->assertEquals(
            'I should see the error message "Login or Password incorrect"',
            $steps[7]->getText()
        );
    }

    public function test180()
    {
        $feature = $this->loadFeature('180.feature');

        $this->assertEquals('Cucumber command line', $feature->getTitle());
        $this->assertEquals(
            array(
                'In order to write better software',
                'Developers should be able to execute requirements as tests'
            ),
            $feature->getDescription()
        );
        $this->assertTrue($feature->hasScenarios());
        $this->assertEquals(1, count($feature->getScenarios()));
        $this->assertEquals(
            'Pending Scenario at the end of a file with whitespace after it',
            end($feature->getScenarios())->getTitle()
        );
        $this->assertFalse(end($feature->getScenarios())->hasSteps());
    }

    public function test236()
    {
        $feature = $this->loadFeature('236.feature');

        $this->assertEquals('Unsubstituted argument placeholder', $feature->getTitle());
        $this->assertFalse($feature->hasDescription());
        $this->assertTrue($feature->hasScenarios());
        $this->assertEquals(1, count($feature->getScenarios()));

        $scenario = end($feature->getScenarios());
        $this->assertEquals(
            'See Annual Leave Details (as Management & Human Resource)',
            $scenario->getTitle()
        );
        $this->assertTrue($scenario->hasSteps());
        $this->assertEquals(1, count($scenario->getSteps()));
        $this->assertEquals(
            array(
                array('role' => 'HUMAN RESOURCE')
            ),
            $scenario->getExamples()->getTable()->getHash()
        );

        $step = end($scenario->getSteps());
        $this->assertEquals('Given', $step->getType());
        $this->assertEquals('the following users exist in the system', $step->getText());
        $this->assertEquals(
            array(
                array('name' => 'Jane', 'email' => 'jane@fmail.com', 'role_assignments' => '<role>', 'group_memberships' => 'Sales (manager)'),
                array('name' => 'Max', 'email' => 'max@fmail.com', 'role_assignments' => '', 'group_memberships' => 'Sales (member)'),
                array('name' => 'Carol', 'email' => 'carol@fmail.com', 'role_assignments' => '', 'group_memberships' => 'Sales (member)'),
                array('name' => 'Cat', 'email' => 'cat@fmail.com', 'role_assignments' => '', 'group_memberships' => ''),
            ),
            end($step->getArguments())->getHash()
        );
    }

    public function test241()
    {
        $feature = $this->loadFeature('241.feature');

        $this->assertEquals('Using the Console Formatter', $feature->getTitle());
        $this->assertEquals(
            array(
                'In order to verify this error',
                'I want to run this feature using the progress format',
                'So that it can be fixed'
            ),
            $feature->getDescription()
        );

        $this->assertTrue($feature->hasScenarios());
        $this->assertEquals(1, count($feature->getScenarios()));
        $this->assertEquals('A normal feature', end($feature->getScenarios())->getTitle());

        $steps = end($feature->getScenarios())->getSteps();
        $this->assertEquals(3, count($steps));

        $this->assertEquals('Given', $steps[0]->getType());
        $this->assertEquals('I have a pending step', $steps[0]->getText());

        $this->assertEquals('When', $steps[1]->getType());
        $this->assertEquals('I run this feature with the progress format', $steps[1]->getText());

        $this->assertEquals('Then', $steps[2]->getType());
        $this->assertEquals("I should get a no method error for 'backtrace_line'", $steps[2]->getText());
    }

    public function test246()
    {
        $feature = $this->loadFeature('246.feature');

        $this->assertEquals('https://rspec.lighthouseapp.com/projects/16211/tickets/246-distorted-console-output-for-slightly-complicated-step-regexp-match', $feature->getTitle());
        $this->assertEquals('See "No Record(s) Found" for Zero Existing', end($feature->getScenarios())->getTitle());
        $this->assertEquals(1, count(end($feature->getScenarios())->getSteps()));
    }

    public function testRuAddition()
    {
        $feature = $this->loadFeature('ru_addition.feature');

        $this->assertEquals('Сложение чисел', $feature->getTitle());
        $this->assertEquals(
            array(
                'Чтобы не складывать в уме',
                'Все, у кого с этим туго',
                'Хотят автоматическое сложение целых чисел'
            ),
            $feature->getDescription()
        );

        $this->assertTrue($feature->hasScenarios());
        $this->assertEquals(1, count($feature->getScenarios()));

        $scenario = end($feature->getScenarios());

        $this->assertEquals('Сложение двух целых чисел', $scenario->getTitle());

        $steps = $scenario->getSteps();

        $this->assertEquals(4, count($steps));

        $this->assertEquals('Допустим', $steps[0]->getType());
        $this->assertEquals('я ввожу число 50', $steps[0]->getText());

        $this->assertEquals('И', $steps[1]->getType());
        $this->assertEquals('затем ввожу число 70', $steps[1]->getText());

        $this->assertEquals('Если', $steps[2]->getType());
        $this->assertEquals('я нажимаю "+"', $steps[2]->getText());

        $this->assertEquals('То', $steps[3]->getType());
        $this->assertEquals('результатом должно быть число 120', $steps[3]->getText());
    }

    public function testAddition()
    {
        $feature = $this->loadFeature('addition.feature');
        $this->assertEquals(
            array(
                'In order to avoid silly mistakes',
                'As a math idiot',
                'I want to be told the sum of two numbers'
            ),
            $feature->getDescription()
        );

        $this->assertTrue($feature->hasScenarios());
        $this->assertEquals(2, count($feature->getScenarios()));

        $scenarios = $feature->getScenarios();

        $this->assertEquals('Add two numbers', $scenarios[0]->getTitle());
        $steps = $scenarios[0]->getSteps();
        $this->assertEquals(4, count($steps));

        $this->assertEquals('Given', $steps[0]->getType());
        $this->assertEquals('I have entered 11 into the calculator', $steps[0]->getText());

        $this->assertEquals('And', $steps[1]->getType());
        $this->assertEquals('I have entered 12 into the calculator', $steps[1]->getText());

        $this->assertEquals('When', $steps[2]->getType());
        $this->assertEquals('I press add', $steps[2]->getText());

        $this->assertEquals('Then', $steps[3]->getType());
        $this->assertEquals('the result should be 23 on the screen', $steps[3]->getText());

        $this->assertEquals('Div two numbers', $scenarios[1]->getTitle());
        $steps = $scenarios[1]->getSteps();
        $this->assertEquals(4, count($steps));

        $this->assertEquals('Given', $steps[0]->getType());
        $this->assertEquals('I have entered 10 into the calculator', $steps[0]->getText());

        $this->assertEquals('And', $steps[1]->getType());
        $this->assertEquals('I have entered 2 into the calculator', $steps[1]->getText());

        $this->assertEquals('When', $steps[2]->getType());
        $this->assertEquals('I press div', $steps[2]->getText());

        $this->assertEquals('Then', $steps[3]->getType());
        $this->assertEquals('the result should be 5 on the screen', $steps[3]->getText());
    }

    public function testEmptyOutline()
    {
        $this->setExpectedException('\Everzet\Gherkin\ParserException');
        $feature = $this->loadFeature('empty_outline.feature');
    }

    public function testTrimPyString()
    {
        $feature = $this->loadFeature('trimpystring.feature');

        $this->assertEquals('   a string
  with something
be
a
u
  ti
    ful', (string) end(end(end($feature->getScenarios())->getSteps())->getArguments()));
    }

    public function testMultiplePyString()
    {
        $feature = $this->loadFeature('multiplepystrings.feature');

        $steps = end($feature->getScenarios())->getSteps();

        $this->assertEquals(2, count($steps));
        $this->assertEquals('   a string
  with something
be
a
u
  ti
    ful', (string) end($steps[0]->getArguments()));
        $this->assertEquals('   a string
  with something
be
a
u
  ti
    ful', (string) end($steps[1]->getArguments()));
    }

    public function testFileGetter()
    {
        $feature = $this->loadFeature('addition.feature');

        $this->assertEquals(null, $feature->getFile());
        $this->assertEquals(null, end($feature->getScenarios())->getFile());

        $feature = $this->loadFeatureFromFile('addition.feature');

        $this->assertEquals('addition.feature', basename($feature->getFile()));
        $this->assertEquals('addition.feature', basename(end($feature->getScenarios())->getFile()));

        $feature = $this->loadFeatureFromFile('tables.feature');

        $this->assertEquals('tables.feature', basename($feature->getFile()));
    }

    public function testI18n()
    {
        foreach (array($this->loadFeature('addition.feature'), $this->loadFeatureFromFile('addition.feature')) as $feature) {
            $this->assertEquals('Feature', $feature->getI18n()->__('feature', 'Feature'));
            $this->assertEquals('Scenario',
                end($feature->getScenarios())->getI18n()->__('scenario', 'Scenario')
            );
        }

        foreach (array($this->loadFeature('ru_addition.feature'), $this->loadFeatureFromFile('ru_addition.feature')) as $feature) {
            $this->assertEquals('Функционал', $feature->getI18n()->__('feature', 'Feature'));
            $this->assertEquals('Сценарий',
                end($feature->getScenarios())->getI18n()->__('scenario', 'Scenario')
            );
        }
    }
}
