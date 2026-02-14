<?php

namespace Behat\Config;

final class TesterOptions
{
    private const TESTERS_SETTINGS_GROUP = 'testers';

    private const CALLS_SETTINGS_GROUP = 'calls';

    private const STRICT_SETTING = 'strict';

    private const STOP_ON_FAILURE_SETTING = 'stop_on_failure';

    private const SKIP_SETTING = 'skip';

    private const ERROR_REPORTING_SETTING = 'error_reporting';

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
}
