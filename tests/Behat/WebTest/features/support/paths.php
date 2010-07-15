<?php

$this->pathTo = function($page) {
    switch ($page) {
        case 'главная':
            return 'http://www.onliner.by/';
        case 'каталог':
            return 'http://catalog.onliner.by/';
        default:
            return 'http://www.onliner.by/';
    }
};
