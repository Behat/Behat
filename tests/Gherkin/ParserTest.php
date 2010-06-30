<?php

require_once __DIR__ . '/../bootstrap.php';

class ParserTest extends \PHPUnit_Framework_TestCase
{
    private function loadFeature($path)
    {
        $parser = new \Gherkin\Parser;
        return $parser->parse(file_get_contents(__DIR__ . '/../fixtures/features/' . $path));
    }

    public function testDosLineEndingsFeature()
    {
        $feature = $this->loadFeature('dos_line_endings.feature');

        $this->assertEquals('DOS line endings', $feature->getTitle());
        $this->assertTrue($feature->hasDescription());
        $this->assertFalse($feature->hasBackgrounds());
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
        $this->assertTrue($feature->hasBackgrounds());

        $background = end($feature->getBackgrounds());
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
        $this->assertEquals(array(array('state' => 'missing')), $scenarios[1]->getExamples());

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
            array(array(array('foo' => 'bar', 'bar' => 'baz'))),
            $steps[1]->getArguments()
        );

        $this->assertTrue($outline->hasExamples());
        $this->assertEquals(
            array(
                array('a' => '1', 'b' => '2', 'c' => '3'),
                array('a' => '2', 'b' => '3', 'c' => '4'),
            ),
            $outline->getExamples()
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
        $this->assertEquals(array('a string'), $step->getArguments());
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
        $this->assertEquals(array('example'), $step->getArguments());

        $this->assertEquals('table', $scenarios[1]->getTitle());
        $this->assertEquals(1, count($scenarios[0]->getSteps()));

        $step = end($scenarios[1]->getSteps());
        $this->assertEquals('Given', $step->getType());
        $this->assertEquals('a table', $step->getText());
        $this->assertEquals(array(array(array('table' => 'example'))), $step->getArguments());
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
            $outline->getExamples()
        );
    }
}
