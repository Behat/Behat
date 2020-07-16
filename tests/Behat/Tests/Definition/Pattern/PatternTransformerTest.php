<?php

namespace Behat\Tests\Definition\Pattern;

use Behat\Behat\Definition\Pattern\PatternTransformer;
use Behat\Behat\Definition\Pattern\Policy\PatternPolicy;
use PHPUnit\Framework\TestCase;

/**
 * Class PatternTransformerTest
 * @author Julien Deniau <julien.deniau@mapado.com>
 */
class PatternTransformerTest extends TestCase
{
    public function testTransformPatternToRegexCache()
    {
        $observer = $this->prophesize('Behat\Behat\Definition\Pattern\Policy\PatternPolicy');
        // first pattern
        $observer->supportsPattern('hello world')->willReturn(true);
        $observer->transformPatternToRegex('hello world')
            ->shouldBeCalledTimes(1)
            ->willReturn('/hello world/');

        // second pattern
        $observer->supportsPattern('hi world')->willReturn(true);
        $observer->transformPatternToRegex('hi world')
            ->shouldBeCalledTimes(1)
            ->willReturn('/hi world/');

        $testedInstance = new PatternTransformer();
        $testedInstance->registerPatternPolicy($observer->reveal());
        $regex = $testedInstance->transformPatternToRegex('hello world');
        $regex2 = $testedInstance->transformPatternToRegex('hello world');

        $regex3 = $testedInstance->transformPatternToRegex('hi world');

        $this->assertEquals('/hello world/', $regex);
        $this->assertEquals('/hello world/', $regex2);
        $this->assertEquals('/hi world/', $regex3);
    }

    public function testTransformPatternToRegexCacheAndRegisterNewPolicy()
    {
        // first pattern
        $policy1Prophecy = $this->prophesize('Behat\Behat\Definition\Pattern\Policy\PatternPolicy');
        $policy1Prophecy->supportsPattern('hello world')->willReturn(true);
        $policy1Prophecy->transformPatternToRegex('hello world')
            ->shouldBeCalledTimes(2)
            ->willReturn('/hello world/');

        // second pattern
        $policy2Prophecy = $this->prophesize('Behat\Behat\Definition\Pattern\Policy\PatternPolicy');
        $policy1Prophecy->supportsPattern()->shouldNotBeCalled();
        $policy1Prophecy->transformPatternToRegex()->shouldNotBeCalled();

        $testedInstance = new PatternTransformer();
        $testedInstance->registerPatternPolicy($policy1Prophecy->reveal());
        $regex = $testedInstance->transformPatternToRegex('hello world');
        $regex2 = $testedInstance->transformPatternToRegex('hello world');

        $testedInstance->registerPatternPolicy($policy2Prophecy->reveal());
        $regex3 = $testedInstance->transformPatternToRegex('hello world');

        $this->assertEquals('/hello world/', $regex);
        $this->assertEquals('/hello world/', $regex2);
        $this->assertEquals('/hello world/', $regex3);
    }

    public function testTransformPatternToRegexNoMatch()
    {
        // first pattern
        $policy1Prophecy = $this->prophesize('Behat\Behat\Definition\Pattern\Policy\PatternPolicy');
        $policy1Prophecy->supportsPattern('hello world')->willReturn(false);
        $policy1Prophecy->transformPatternToRegex('hello world')
            ->shouldNotBeCalled();


        $testedInstance = new PatternTransformer();
        $testedInstance->registerPatternPolicy($policy1Prophecy->reveal());
        $this->expectException('\Behat\Behat\Definition\Exception\UnknownPatternException');
        $this->expectExceptionMessage("Can not find policy for a pattern `hello world`.");
        $regex = $testedInstance->transformPatternToRegex('hello world');
    }
}
