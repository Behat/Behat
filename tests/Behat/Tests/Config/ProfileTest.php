<?php

declare(strict_types=1);

namespace Behat\Tests\Config;

use Behat\Config\Extension;
use Behat\Config\Filter\NameFilter;
use Behat\Config\Filter\TagFilter;
use Behat\Config\Formatter\JUnitFormatter;
use Behat\Config\Formatter\PrettyFormatter;
use Behat\Config\Formatter\ProgressFormatter;
use Behat\Config\Formatter\ShowOutputOption;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\Testwork\Output\Printer\Factory\OutputFactory;
use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;
use PHPUnit\Framework\TestCase;

final class ProfileTest extends TestCase
{
    public function testProfileCanBeConvertedIntoAnArray(): void
    {
        $profile = new Profile('default');

        $this->assertIsArray($profile->toArray());
    }

    public function testItReturnsSettings(): void
    {
        $settings = [
            'extensions' => [
                'some_extension' => [],
            ],
        ];

        $profile = new Profile('default', $settings);

        $this->assertEquals($settings, $profile->toArray());
    }

    public function testAddingExtensions(): void
    {
        $profile = new Profile('default');
        $profile->withExtension(new Extension('Behat\MinkExtension', ['base_url' => 'https://127.0.0.1:8080']));
        $profile->withExtension(new Extension('FriendsOfBehat\MinkDebugExtension', ['directory' => 'etc/build']));

        $this->assertEquals([
            'extensions' => [
                'Behat\MinkExtension' => [
                    'base_url' => 'https://127.0.0.1:8080',
                ],
                'FriendsOfBehat\MinkDebugExtension' => [
                    'directory' => 'etc/build',
                ],
            ],
        ], $profile->toArray());
    }

    public function testItThrowsAnExceptionWhenAddingExistingExtension(): void
    {
        $profile = new Profile('default');

        $profile->withExtension(new Extension('custom_extension'));

        $this->expectException(ConfigurationLoadingException::class);
        $this->expectExceptionMessage('The extension "custom_extension" already exists.');

        $profile->withExtension(new Extension('custom_extension'));
    }

    public function testAddingSuites(): void
    {
        $profile = new Profile('default');
        $profile
            ->withSuite(new Suite('admin_dashboard'))
            ->withSuite(new Suite('managing_administrators'))
        ;

        $this->assertEquals([
            'suites' => [
                'admin_dashboard' => [],
                'managing_administrators' => [],
            ],
        ], $profile->toArray());
    }

    public function testItThrowsAnExceptionWhenAddingExistingSuite(): void
    {
        $profile = new Profile('default');

        $profile->withSuite(new Suite('admin_dashboard'));

        $this->expectException(ConfigurationLoadingException::class);
        $this->expectExceptionMessage('The suite "admin_dashboard" already exists.');

        $profile->withSuite(new Suite('admin_dashboard'));
    }

    public function testAddingFilters(): void
    {
        $profile = new Profile('default');
        $profile
            ->withFilter(new TagFilter('admin'))
            ->withFilter(new NameFilter('Managing administrators'))
        ;

        $this->assertEquals([
            'gherkin' => [
                'filters' => [
                    'tags' => 'admin',
                    'name' => 'Managing administrators',
                ],
            ],
        ], $profile->toArray());
    }

    public function testItThrowsAnExceptionWhenAddingExistingFilter(): void
    {
        $profile = new Profile('default');

        $profile->withFilter(new TagFilter('tag1'));

        $this->expectException(ConfigurationLoadingException::class);
        $this->expectExceptionMessage('The filter "tags" already exists.');

        $profile->withFilter(new TagFilter('tag1'));
    }

    public function testAddingFormatters(): void
    {
        $profile = new Profile('default');

        $profile
            ->withFormatter((new PrettyFormatter(expand: true, paths: false))->withOutputVerbosity(OutputFactory::VERBOSITY_VERBOSE))
            ->withFormatter(new ProgressFormatter(timer: false))
            ->withFormatter(new JUnitFormatter())
        ;

        $this->assertEquals([
            'formatters' => [
                'pretty' => [
                    'timer' => true,
                    'expand' => true,
                    'paths' => false,
                    'multiline' => true,
                    'output_verbosity' => 2,
                    'show_output' => 'yes',
                    'short_summary' => true,
                    'print_skipped_steps' => true,
                ],
                'progress' => [
                    'timer' => false,
                    'show_output' => 'in-summary',
                    'short_summary' => false,
                ],
                'junit' => [
                    'timer' => true,
                ],
            ],
        ], $profile->toArray());
    }

    public function testDisablingFormatters(): void
    {
        $profile = new Profile('default');

        $profile->disableFormatter(PrettyFormatter::NAME);
        $profile->disableFormatter(ProgressFormatter::NAME);
        $profile->disableFormatter(JUnitFormatter::NAME);

        $this->assertEquals([
            'formatters' => [
                'pretty' => false,
                'progress' => false,
                'junit' => false,
            ],
        ], $profile->toArray());
    }

    public function testSettingShowOutputOption(): void
    {
        $profile = new Profile('default');

        $profile->disableFormatter(PrettyFormatter::NAME);
        $profile->disableFormatter(JUnitFormatter::NAME);
        $profile->withFormatter(new ProgressFormatter(showOutput: ShowOutputOption::Yes));

        $this->assertEquals([
            'formatters' => [
                'pretty' => false,
                'progress' => [
                    'timer' => true,
                    'show_output' => 'yes',
                    'short_summary' => false,
                ],
                'junit' => false,
            ],
        ], $profile->toArray());
    }
}
