<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Testwork\Translator\ServiceContainer\TranslatorExtension;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withExtension(
                new Extension(TranslatorExtension::class, [
                    'locale' => 'fr',
                ])
            )
    );
