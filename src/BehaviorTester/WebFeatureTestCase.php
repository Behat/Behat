<?php

namespace BehaviorTester;

abstract class WebFeatureTestCase extends FeatureTestCase
{
    protected $client;
    protected $response;
    protected $form = array();

    public function getClient()
    {
        if (null === $this->client) {
            $this->client = new WebClient;
        }

        return $this->client;
    }

    public function setResponse(\Symfony\Components\DomCrawler\Crawler $response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function visit($url)
    {
        $this->setResponse($this->getClient()->request('GET', $url));
    }

    abstract public function pathTo($page);

    protected function initStepDefinition()
    {
        $iterator = new \RecursiveDirectoryIterator(
            __DIR__ . '/steps/',
            \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
        );
        $iterator = new \RecursiveIteratorIterator(
            $iterator, \RecursiveIteratorIterator::SELF_FIRST
        );

        $this->steps = array();
        foreach ($iterator as $file) {
            require $file;
        }
    }

    public function stepDebug()
    {
        if (null !== $this->getResponse()) {
            echo $this->getResponse()->text();
        }
    }

    public function stepIAmOnThe($page)
    {
        $this->visit($this->pathTo($page));
    }

    public function stepIFollowTheLink($link)
    {
        $links = $this->getResponse()->filter('a:contains("' . $link . '")');
        $this->assertGreaterThan(1, $links->count(), sprintf('Page has "%s" link', $link));

        $link = $links->eq(1)->link();
        $this->setResponse($this->getClient()->click($link));
    }

    public function stepIFillInWith($field, $value)
    {
        $this->form[$field] = $value;
    }

    public function stepISelectFrom($value, $field)
    {
        $this->stepIFillInWith($field, $value);
    }

    public function stepICheck($field)
    {
        $this->stepIFillInWith($field, true);
    }

    public function stepIUncheck($field)
    {
        $this->stepIFillInWith($field, false);
    }

    public function stepIAttachTheFileAtTo($path, $field)
    {
        $this->stepIFillInWith($field, $path);
    }

    public function stepIPress($button)
    {
        $form = $this->getResponse()->selectButton($button);
        $this->setResponse($this->getClient()->submit($form, $this->form));
        $this->form = array();
    }

    public function stepIShouldSee($text)
    {
        $this->assertContains($text, $this->getResponse()->text());
    }

    public function stepIShouldNotSee($text)
    {
        $this->assertNotContains($text, $this->getResponse()->text());
    }
}