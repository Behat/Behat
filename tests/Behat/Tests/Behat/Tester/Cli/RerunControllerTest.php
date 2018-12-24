<?php

namespace Behat\Tests\Behat\Tester\Cli;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\Tester\Cli\RerunController;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RerunControllerTest extends TestCase
{
    /** @var EventDispatcherInterface|MockObject */
    private $eventDispatcher;

    /** @var Input */
    private $input;

    /** @var string|null */
    private $rerunCacheFile;

    protected function setUp()
    {
        $this->eventDispatcher =
            $this->getMockBuilder('\Symfony\Component\EventDispatcher\EventDispatcherInterface')
                ->disableOriginalConstructor()
                ->getMock();

        $inputDefinition = new InputDefinition();
        $inputDefinition->setOptions(array(
            new InputOption('rerun'),
            new InputOption('profile'),
            new InputOption('suite'),
            new InputOption('name'),
            new InputOption('tags'),
            new InputOption('role'),
        ));
        $inputDefinition->setArguments(array(
            new InputArgument('paths')
        ));

        $this->input = new ArrayInput(array('--profile' => 'behat'), $inputDefinition);
    }

    protected function tearDown()
    {
        if ($this->rerunCacheFile && file_exists($this->rerunCacheFile)) {
            @unlink($this->rerunCacheFile);
        }
    }


    public function testExecuteWithoutRerunOptionMustNotAddListeners()
    {
        $output = new BufferedOutput();

        $cachePath = sys_get_temp_dir();
        $basePath = sys_get_temp_dir();

        $subject = new RerunController($this->eventDispatcher, $cachePath, $basePath);

        $this->input->setOption('rerun', false);

        // void
        $this->assertNull($subject->execute($this->input, $output));
    }

    public function testExecuteWithoutCachePathMustNotAddListeners()
    {

        $output = new BufferedOutput();

        $basePath = sys_get_temp_dir();

        $subject = new RerunController($this->eventDispatcher, null, $basePath);

        $this->input->setOption('rerun', true);
        $this->input->setOption('suite', false);
        $this->input->setOption('name', array(null, null));
        $this->input->setOption('tags', array(null, null));
        $this->input->setOption('role', false);
        $this->input->setArgument('paths', false);

        // void
        $this->assertNull($subject->execute($this->input, $output));
    }

    public function testExecuteMustAddListenersAndMustNotRewritePathsArgument()
    {
        $output = new BufferedOutput();

        $basePath = $cachePath = sys_get_temp_dir();

        $subject = new RerunController($this->eventDispatcher, $cachePath, $basePath);

        $this->input->setOption('rerun', true);
        $this->input->setOption('suite', false);
        $this->input->setOption('name', array(null, null));
        $this->input->setOption('tags', array(null, null));
        $this->input->setOption('role', false);
        $this->input->setArgument('paths', false);

        $this->eventDispatcher
            ->expects($this->exactly(3))
            ->method('addListener')
            ->withConsecutive(
                array(ScenarioTested::AFTER, array($subject, 'collectFailedScenario'), $this->anything()),
                array(ExampleTested::AFTER, array($subject, 'collectFailedScenario'), $this->anything()),
                array(ExerciseCompleted::AFTER, array($subject, 'writeCache'), $this->anything())
            )
        ;

        // void
        $this->assertNull($subject->execute($this->input, $output));
        $this->assertFalse($this->input->getArgument('paths'));
    }

    public function testExecuteMustAddListenersAndMustRewritePathsArgumentIfFileExists()
    {
        $output = new BufferedOutput();

        $basePath = $cachePath = sys_get_temp_dir();

        $subject = new RerunController($this->eventDispatcher, $cachePath, $basePath);

        $this->input->setOption('rerun', true);
        $this->input->setOption('suite', false);
        $this->input->setOption('name', array(null, null));
        $this->input->setOption('tags', array(null, null));
        $this->input->setOption('role', false);
        $this->input->setArgument('paths', false);

        $this->eventDispatcher
            ->expects($this->exactly(3))
            ->method('addListener')
            ->withConsecutive(
                array(ScenarioTested::AFTER, array($subject, 'collectFailedScenario'), $this->anything()),
                array(ExampleTested::AFTER, array($subject, 'collectFailedScenario'), $this->anything()),
                array(ExerciseCompleted::AFTER, array($subject, 'writeCache'), $this->anything())
            )
        ;

        $key = md5(
            'behat' .
            false .
            implode(' ', array(null, null)) .
            implode(' ', array(null, null)) .
            false .
            false .
            $basePath
        );

        $this->rerunCacheFile = $cachePath . DIRECTORY_SEPARATOR . $key . '.rerun';
        touch($this->rerunCacheFile);

        // void
        $this->assertNull($subject->execute($this->input, $output));
        $this->assertEquals($this->rerunCacheFile, $this->input->getArgument('paths'));
    }
}
