<?php

namespace Everzet\Gherkin\Node;

/*
 * This file is part of the Gherkin.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Section.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class SectionNode
{
    protected $title = '';
    protected $tags = array();
    protected $locale;
    protected $file;

    public function __construct($locale, $file = null)
    {
        $this->locale = $locale;
        $this->file = $file;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function addTag($tag)
    {
        $this->tags[] = $tag;
    }

    public function addTags(array $tags)
    {
        $this->tags = array_merge($this->tags, $tags);
    }

    public function hasTags()
    {
        return count($this->tags) > 0;
    }

    public function hasTag($tag)
    {
        return in_array($tag, $this->tags);
    }

    public function getTags()
    {
        return $this->tags;
    }
}

