<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile((new Profile('default'))
        ->withSuite((new Suite('default', [
            'services' => [
                'shared_service' => [
                    'class' => 'SharedService',
                ],
            ],
        ]))
            ->addContext('FirstContext', ['@shared_service'])
            ->addContext('SecondContext', ['@shared_service'])
        )
    );
