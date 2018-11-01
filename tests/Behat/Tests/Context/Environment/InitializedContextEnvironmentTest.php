<?php

namespace Behat\Tests\Context\Environment;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Context\Exception\ContextNotFoundException;
use Behat\Testwork\Suite\Suite;
use PHPUnit\Framework\TestCase;

interface ContextInterface {}
class BaseContext implements Context {}
class ExtendingContext extends BaseContext implements ContextInterface {}

class InitializedContextEnvironmentTest extends TestCase
{
    public function testHasContextClassConcrete()
    {
      $environment = new InitializedContextEnvironment(self::getMock(Suite::class));
      $environment->registerContext(new BaseContext());

      self::assertTrue($environment->hasContextClass(BaseContext::class));
    }

    public function testHasContextClassExtended()
    {
      $environment = new InitializedContextEnvironment(self::getMock(Suite::class));
      $environment->registerContext(new ExtendingContext());

      self::assertTrue($environment->hasContextClass(BaseContext::class));
    }

    public function testHasContextClassInterface()
    {
      $environment = new InitializedContextEnvironment(self::getMock(Suite::class));
      $environment->registerContext(new ExtendingContext());

      self::assertTrue($environment->hasContextClass(ContextInterface::class));
    }

    public function testHasContextClassMissing()
    {
      $environment = new InitializedContextEnvironment(self::getMock(Suite::class));

      self::assertFalse($environment->hasContextClass(ContextInterface::class));
    }

    public function testResolveContextClassConcrete()
    {
      $environment = new InitializedContextEnvironment(self::getMock(Suite::class));

      $baseContext = new BaseContext();
      $environment->registerContext($baseContext);

      self::assertEquals($baseContext, $environment->resolveContextClass(BaseContext::class));
    }

    public function testResolveContextClassExtended()
    {
      $environment = new InitializedContextEnvironment(self::getMock(Suite::class));

      $extendingContext = new ExtendingContext();
      $environment->registerContext($extendingContext);

      self::assertEquals($extendingContext, $environment->resolveContextClass(BaseContext::class));
    }

    public function testResolveContextClassInterface()
    {
      $environment = new InitializedContextEnvironment(self::getMock(Suite::class));

      $extendingContext = new ExtendingContext();
      $environment->registerContext($extendingContext);

      self::assertEquals($extendingContext, $environment->resolveContextClass(ContextInterface::class));
    }

    public function testResolveContextClassMissing() {
      $environment = new InitializedContextEnvironment(self::getMock(Suite::class));

      self::assertNull($environment->resolveContextClass(BaseContext::class));
    }

    public function testGetContextConcrete()
    {
      $environment = new InitializedContextEnvironment(self::getMock(Suite::class));

      $baseContext = new BaseContext();
      $environment->registerContext($baseContext);

      self::assertEquals($baseContext, $environment->getContext(BaseContext::class));
    }

    public function testGetContextExtended()
    {
      $environment = new InitializedContextEnvironment(self::getMock(Suite::class));

      $extendingContext = new ExtendingContext();
      $environment->registerContext($extendingContext);

      self::assertEquals($extendingContext, $environment->getContext(BaseContext::class));
    }

    public function testGetContextInterface()
    {
      $environment = new InitializedContextEnvironment(self::getMock(Suite::class));

      $extendingContext = new ExtendingContext();
      $environment->registerContext($extendingContext);

      self::assertEquals($extendingContext, $environment->getContext(ContextInterface::class));
    }

    public function testGetContextMissing()
    {
      self::setExpectedException(
          ContextNotFoundException::class,
          '`' . BaseContext::class . '` context is not found in the suite environment. Have you registered it?'
      );

      $environment = new InitializedContextEnvironment(self::getMock(Suite::class));
      $environment->getContext(BaseContext::class);
    }
}
