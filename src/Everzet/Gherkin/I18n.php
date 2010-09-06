<?php

namespace Everzet\Gherkin;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * I18n.
 *
 * @package     Gherkin
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class I18n
{
    protected $xliffPath;
    protected $xliffFilesExtension;
    protected $values = array();

    public function __construct($xliffPath, $xliffFilesExtension = '.xml')
    {
        if (!is_dir($xliffPath)) {
            throw new \InvalidArgumentException(sprintf('Path "%s" is invalid', $xliffPath));
        }

        $this->xliffPath = $xliffPath;
        $this->xliffFilesExtension = $xliffFilesExtension;
    }

    public function loadLang($lang)
    {
        $filename = sprintf('%s/%s%s',
            $this->xliffPath, $lang, $this->xliffFilesExtension
        );

        libxml_use_internal_errors(true);
        if (!$xml = simplexml_load_file($filename)) {
            return false;
        }
        libxml_use_internal_errors(false);

        $translationUnit = $xml->xpath('//trans-unit');
        $translations = array();

        foreach ($translationUnit as $unit) {
            $source = (string) $unit->source;
            $target = (string) $unit->target;
            if (false !== strpos($target, '|')) {
                $target = explode('|', $target);
            }
            $translations[$source] = $target;
        }

        $this->values = $translations;
    }

    public function __($key, $default = null)
    {
        if (isset($this->values[$key])) {
            return $this->values[$key];
        }

        return $default;
    }
}
