<?php

use Behat\Config\Config;
use Behat\Config\Profile;

return (new Config())
    ->withProfile(new Profile('default'))
    ->withProfile((new Profile('editor_url'))
        ->withPathOptions(editorUrl: 'phpstorm://open?file={relPath}&line={line}')
    );
