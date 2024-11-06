<?php

namespace Behat\Tests\Definition\Pattern;

use Behat\Behat\Definition\Pattern\PatternTransformer;
use Behat\Behat\Definition\Pattern\Policy\PatternPolicy;
use PHPUnit\Framework\TestCase;
use Behat\Behat\Definition\Exception\UnknownPatternException;

/**
 * @author Julien Deniau <julien.deniau@mapado.com>
 */
class PatternTransformerTest extends TestCase
{
    public function testTransformPatternToRegexCache(): void
    {
        $policy = $this->createStub(PatternPolicy::class);
        $policy->method('supportsPattern')
            ->willReturn(true);
        $policy->method('transformPatternToRegex')
            ->will($this->returnValueMap([
                ['hello world', '/hello world/'],
                ['hi world', '/hi world/'],
            ]));

        $testedInstance = new PatternTransformer();
        $testedInstance->registerPatternPolicy($policy);

        $regex = $testedInstance->transformPatternToRegex('hello world');
        $regex2 = $testedInstance->transformPatternToRegex('hello world');
        $regex3 = $testedInstance->transformPatternToRegex('hi world');

        $this->assertEquals('/hello world/', $regex);
        $this->assertEquals('/hello world/', $regex2);
        $this->assertEquals('/hi world/', $regex3);
    }

    public function testTransformPatternToRegexCacheAndRegisterNewPolicy(): void
    {
        // first pattern
        $policy1 = $this->createMock(PatternPolicy::class);
        $policy1->method('supportsPattern')->willReturn(true);
        $policy1->expects($this->exactly(2))
            ->method('transformPatternToRegex')
            ->with($this->equalTo('hello world'))
            ->willReturn('/hello world/');

        // second pattern
        $policy2 = $this->createMock(PatternPolicy::class);
        $policy2->expects($this->never())->method('supportsPattern');
        $policy2->expects($this->never())->method('transformPatternToRegex');

        $testedInstance = new PatternTransformer();
        $testedInstance->registerPatternPolicy($policy1);
        $regex = $testedInstance->transformPatternToRegex('hello world');
        $regex2 = $testedInstance->transformPatternToRegex('hello world');

        $testedInstance->registerPatternPolicy($policy2);
        $regex3 = $testedInstance->transformPatternToRegex('hello world');

        $this->assertEquals('/hello world/', $regex);
        $this->assertEquals('/hello world/', $regex2);
        $this->assertEquals('/hello world/', $regex3);
    }

    public function testTransformPatternToRegexNoMatch(): void
    {
        $policy = $this->createMock(PatternPolicy::class);
        $policy->method('supportsPattern')
            ->with($this->equalTo('hello world'))
            ->willReturn(false);
        $policy->expects($this->never())
            ->method('transformPatternToRegex');

        $testedInstance = new PatternTransformer();
        $testedInstance->registerPatternPolicy($policy);

        $this->expectException(UnknownPatternException::class);
        $this->expectExceptionMessage("Can not find policy for a pattern `hello world`.");
        $regex = $testedInstance->transformPatternToRegex('hello world');
    }
}
