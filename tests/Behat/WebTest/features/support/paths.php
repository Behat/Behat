<?php

$this->pathTo = function($page) {
    switch ($page) {
        case 'главная':
            return 'http://www.onliner.by/';
        case 'каталог':
            return 'http://catalog.onliner.by/';
        case 'homepage':
            return 'http://everzet.com/';
        case 'about page':
            return 'http://everzet.com/about';
        default:
            return 'localhost';
    }
};
