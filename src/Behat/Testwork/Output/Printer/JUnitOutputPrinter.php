<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Printer;

use Behat\Testwork\Output\Printer\Helper\JUnitXsdChecker;
use Behat\Testwork\Output\Exception\BadOutputPathException;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * A convient wrapper around the StreamOutputPrinter to write valid JUnit 
 * reports.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class JUnitOutputPrinter extends StreamOutputPrinter
{
    /**
     * @var null|string
     */
    private $fileName;
    /**
     * @var JUnitXsdChecker
     */
    private $xsdChecker;
    /**
     * @var boolean
     */
    private $testsuitesNodeOpen = false;
    /**
     * @var boolean
     */
    private $testsuiteNodeOpen = false;
    /**
     * @var boolean
     */
    private $testcaseNodeOpen = false;
    /**
     * @var boolean
     */
    private $testcaseNodeHasChildren = false;

    public function __construct(JUnitXsdChecker $xsdChecker)
    {
        $this->xsdChecker = $xsdChecker;
    }

    /**
     * Creates a new JUnit file.
     *
     * The file will be initialized with an XML definition and the root element.
     *
     * @param string $name                 The filename (without extension) and default value of the name attribute
     * @param array  $testsuitesAttributes Attributes for the root element
     */
    public function createNewFile($name, array $testsuitesAttributes = array())
    {
        $this->flush();

        $this->setFileName(trim(preg_replace('/[^[:alnum:]]+/', '-', $name), '-'));

        $this->xsdChecker->validateTestsuitesAttributes($testsuitesAttributes);

        $this->getWritingStream()->writeln(array(
            '<?xml version="1.0" encoding="UTF-8" ?>',
            sprintf(
                '<testsuites%s>',
                $this->printAttributes(array_merge(array('name' => $name), $testsuitesAttributes))
            ),
        ));

        $this->testsuitesNodeOpen = true;
    }

    public function closeOpenTestsuites()
    {
        if ($this->testsuitesNodeOpen) {
            $this->getWritingStream()->writeln('</testsuites>');
        }
    }

    /**
     * Adds a new <testsuite> node.
     *
     * @param array $testsuiteAttributes
     */
    public function addTestsuite(array $testsuiteAttributes = array())
    {
        $this->closeOpenTestsuite();

        $this->xsdChecker->validateTestsuiteAttributes($testsuiteAttributes);

        $this->getWritingStream()->writeln(sprintf(
            '    <testsuite%s>',
            $this->printAttributes($testsuiteAttributes)
        ));

        $this->testsuiteNodeOpen = true;
    }

    public function closeOpenTestsuite()
    {
        if ($this->testsuiteNodeOpen) {
            $this->getWritingStream()->writeln('    </testsuite>');
        }
    }

    /**
     * Adds a new <testcase> node.
     *
     * @param array $testcaseAttributes
     */
    public function addTestcase(array $testcaseAttributes = array())
    {
        $this->closeOpenTestcase();

        $this->xsdChecker->validateTestcaseAttributes($testcaseAttributes);

        // no new line, since it may have no content
        $this->getWritingStream()->write(sprintf(
            '        <testcase%s',
            $this->printAttributes($testcaseAttributes)
        ));

        $this->testcaseNodeHasChildren = false;
        $this->testcaseNodeOpen = true;
    }

    public function closeOpenTestcase()
    {
        if ($this->testcaseNodeOpen) {
            $this->getWritingStream()->writeln('        </testcase>');
        }
    }

    /**
     * Add a testcase child element.
     *
     * @param string $nodeName
     * @param array  $nodeAttributes
     * @param string $nodeValue
     */
    public function addTestcaseChild($nodeName, array $nodeAttributes = array(), $nodeValue = null)
    {
        $this->validateTestcaseChildNodeName($nodeName);
        $this->validateTestcaseChildAttributes($nodeName, $nodeAttributes);

        $this->getWritingStream()->writeln(array(
            '>', // close testcase open-tag
            sprintf(
                '<%s%s%s%s%s',
                $nodeName,
                $this->printAttributes($nodeAttributes),
                null === $nodeValue ? '/>' : '>',
                $nodeValue,
                null === $nodeValue ? '</'.$nodeName.'>' : null
            ),
        ));

        $this->testcaseNodeHasChildren = true;
    }

    /**
     * Sets file name.
     *
     * @param string $fileName
     * @param string $extension The file extension, defaults to "xml"
     */
    public function setFileName($fileName, $extension = 'xml')
    {
        if ('.'.$extension !== substr($fileName, strlen($extension) + 1)) {
            $fileName .= '.'.$extension;
        }

        $this->fileName = $fileName;
    }

    public function closeAllOpenElements()
    {
        $this->closeOpenTestcase();
        $this->closeOpenTestsuite();
        $this->closeOpenTestsuites();
    }

    /**
     * Prints the attributes in a XML valid way.
     *
     * @param array   $attributes
     * @param boolean $prefixWithSpace
     *
     * @return string
     */
    protected function printAttributes(array $attributes, $prefixWithSpace = true)
    {
        $renderedAttributes = array();
        foreach ($attributes as $name => $value) {
            $renderedAttributes[] = $name.'="'.addslashes($value).'"';
        }

        return ($prefixWithSpace ? ' ' : '') . implode(' ', $renderedAttributes);
    }

    /**
     * {@inheritDoc}
     */
    protected function createOutput($stream = null)
    {
        if (!is_dir($this->outputPath)) {
            mkdir($this->outputPath, 0777, true);
            /*throw new BadOutputPathException(sprintf(
                'Directory expected for the `output_path` option, given `%s`.',
                $this->outputPath
            ), $this->outputPath);*/
        }

        if (null === $this->fileName) {
            throw new \LogicException('Unable to create file, no file name specified');
        }

        $filePath = $this->outputPath.'/'.$this->fileName;

        $stream = new StreamOutput(
            fopen($filePath, 'w'),
            StreamOutput::VERBOSITY_NORMAL,
            false // a file is never decorated
        );
        $this->configureOutputStream($stream);

        return $stream;
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        $this->closeAllOpenElements();
        parent::flush();

        $this->testsuitesNodeOpen = false;
        $this->testsuiteNodeOpen = false;
        $this->testcaseNodeOpen = false;
        $this->testcaseNodeHasChildren = false;
    }
}
