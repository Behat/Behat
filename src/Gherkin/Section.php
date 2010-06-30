<?php

namespace Gherkin;

abstract class Section
{
    protected $title = '';
    protected $tags = array();

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
