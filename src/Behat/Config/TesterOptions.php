<?php

namespace Behat\Config;

use Behat\Config\Converter\ConfigConverterTools;
use PhpParser\Node\Expr;

final class TesterOptions implements ConfigConverterInterface
{
    private const TESTERS_SETTINGS_GROUP = 'testers';

    private const CALLS_SETTINGS_GROUP = 'calls';

    private const STRICT_SETTING = 'strict';

    private const STOP_ON_FAILURE_SETTING = 'stop_on_failure';

    private const SKIP_SETTING = 'skip';

    private const ERROR_REPORTING_SETTING = 'error_reporting';

    private const FUNCTION_NAMES_PER_SETTING = [
        self::CALLS_SETTINGS_GROUP => [
            self::ERROR_REPORTING_SETTING => 'withErrorReporting',
        ],
        self::TESTERS_SETTINGS_GROUP => [
            self::STOP_ON_FAILURE_SETTING => 'withStopOnFailure',
            self::SKIP_SETTING => 'withSkipAllTests',
            self::STRICT_SETTING => 'withStrictResultInterpretation',
        ],
    ];

    /**
     * Used when converting config to PHP, to move relevant Profile settings into this object.
     *
     * @internal
     */
    public static function consumeSettingsFromProfile(&$profileSettings): ?self
    {
        // These settings live under multiple array keys. We don't want to expose/couple these details to the Profile,
        // but we need the Profile to be able to detect whether there *are* any TesterOptions to convert.

        // Build an array of relevant settings groups. Remove any that are found from the settings array in the Profile
        // object, so that the config converter recognises they have been handled.
        $settings = [];
        foreach ([self::TESTERS_SETTINGS_GROUP, self::CALLS_SETTINGS_GROUP] as $group) {
            if (isset($profileSettings[$group])) {
                $settings[$group] = $profileSettings[$group];
                unset($profileSettings[$group]);
            }
        }

        if ($settings === []) {
            // There are no relevant settings, so the config does not need to call ->withTesterOptions on the Profile
            return null;
        }

        return new self($settings);
    }

    public function __construct(
        private array $settings = [],
    ) {
    }

    public function toArray(): array
    {
        return $this->settings;
    }

    /**
     * Behat will convert PHP warnings / errors during steps to exceptions if they match this error_reporting level.
     */
    public function withErrorReporting(int $errorReporting): self
    {
        $this->settings[self::CALLS_SETTINGS_GROUP][self::ERROR_REPORTING_SETTING] = $errorReporting;

        return $this;
    }

    /**
     * Control whether Behat should fail on undefined or pending steps (equivalent to the `--strict` CLI flag).
     */
    public function withStrictResultInterpretation(bool $strict = true): self
    {
        $this->settings[self::TESTERS_SETTINGS_GROUP][self::STRICT_SETTING] = $strict;

        return $this;
    }

    /**
     * Control whether Behat should actually execute steps (equivalent to the `--dry-run` CLI flag).
     */
    public function withSkipAllTests(bool $skip = true): self
    {
        $this->settings[self::TESTERS_SETTINGS_GROUP][self::SKIP_SETTING] = $skip;

        return $this;
    }

    /**
     * Control whether Behat should stop after the first failing scenario (equivalent to `--stop-on-failure` on CLI).
     */
    public function withStopOnFailure(bool $stopOnFailure = true): self
    {
        $this->settings[self::TESTERS_SETTINGS_GROUP][self::STOP_ON_FAILURE_SETTING] = $stopOnFailure;

        return $this;
    }

    public function toPhpExpr(): Expr
    {
        $expr = $optionsObject = ConfigConverterTools::createObject(self::class);

        // Convert recognised settings to their setter functions
        foreach ($this->settings as $group => $groupSettings) {
            foreach ($groupSettings as $settingName => $setting) {
                $functionName = self::FUNCTION_NAMES_PER_SETTING[$group][$settingName] ?? null;
                if ($settingName === self::ERROR_REPORTING_SETTING) {
                    $setting = ConfigConverterTools::errorReportingToConstants($setting);
                }
                if ($functionName) {
                    $expr = ConfigConverterTools::addMethodCall(
                        self::class,
                        $functionName,
                        [$setting],
                        $expr,
                    );
                    unset($this->settings[$group][$settingName]);
                }
            }
        }

        // Remove any now-empty groups (e.g. 'testers' => []) from the settings array
        // Then if necessary provide any remaining settings to the object constructor. These are likely not used by
        // Behat, but we want them in the generated config for the user to review.
        $this->settings = array_filter($this->settings, static fn ($g): bool => $g !== []);
        if ($this->settings !== []) {
            ConfigConverterTools::addArgumentsToConstructor([$this->settings], $optionsObject);
        }

        return $expr;
    }
}
