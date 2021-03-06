<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\{
    ExtractionStrategy\DelegationStrategy,
    ExtractionStrategy,
    Exception\PropertyCannotBeExtracted,
};
use PHPUnit\Framework\TestCase;

class DelegationStrategyTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            ExtractionStrategy::class,
            new DelegationStrategy
        );
    }

    public function testSupports()
    {
        $strategy = new DelegationStrategy(
            $mock1 = $this->createMock(ExtractionStrategy::class),
            $mock2 = $this->createMock(ExtractionStrategy::class)
        );
        $object = new \stdClass;
        $property = 'foo';
        $mock1
            ->expects($this->at(0))
            ->method('supports')
            ->with($object, $property)
            ->willReturn(false);
        $mock1
            ->expects($this->at(1))
            ->method('supports')
            ->with($object, $property)
            ->willReturn(true);
        $mock1
            ->expects($this->at(2))
            ->method('supports')
            ->with($object, $property)
            ->willReturn(false);
        $mock2
            ->expects($this->at(0))
            ->method('supports')
            ->with($object, $property)
            ->willReturn(true);
        $mock2
            ->expects($this->at(1))
            ->method('supports')
            ->with($object, $property)
            ->willReturn(false);

        $this->assertTrue($strategy->supports($object, $property));
        $this->assertTrue($strategy->supports($object, $property));
        $this->assertFalse($strategy->supports($object, $property));
    }

    public function testExtractWithFirstStrategy()
    {
        $strategy = new DelegationStrategy(
            $mock1 = $this->createMock(ExtractionStrategy::class),
            $mock2 = $this->createMock(ExtractionStrategy::class)
        );
        $object = new \stdClass;
        $property = 'foo';
        $mock1
            ->expects($this->once())
            ->method('supports')
            ->with($object, $property)
            ->willReturn(true);
        $mock2
            ->expects($this->never())
            ->method('supports');
        $mock1
            ->expects($this->once())
            ->method('extract')
            ->with($object, $property)
            ->willReturn('baz');
        $mock2
            ->expects($this->never())
            ->method('extract');

        $this->assertSame('baz', $strategy->extract($object, $property));
    }

    public function testCacheStrategy()
    {
        $strategy = new DelegationStrategy(
            $mock1 = $this->createMock(ExtractionStrategy::class),
            $mock2 = $this->createMock(ExtractionStrategy::class)
        );
        $object = new \stdClass;
        $property = 'foo';
        $mock1
            ->expects($this->once())
            ->method('supports')
            ->with($object, $property)
            ->willReturn(true);
        $mock2
            ->expects($this->never())
            ->method('supports');
        $mock1
            ->expects($this->exactly(2))
            ->method('extract')
            ->with($object, $property)
            ->willReturn('baz');
        $mock2
            ->expects($this->never())
            ->method('extract');

        $this->assertSame('baz', $strategy->extract($object, $property));
        $this->assertSame('baz', $strategy->extract($object, $property));
    }

    public function testExtractWithSecondStrategy()
    {
        $strategy = new DelegationStrategy(
            $mock1 = $this->createMock(ExtractionStrategy::class),
            $mock2 = $this->createMock(ExtractionStrategy::class)
        );
        $object = new \stdClass;
        $property = 'foo';
        $mock1
            ->expects($this->once())
            ->method('supports')
            ->with($object, $property)
            ->willReturn(false);
        $mock2
            ->expects($this->once())
            ->method('supports')
            ->with($object, $property)
            ->willReturn(true);
        $mock1
            ->expects($this->never())
            ->method('extract');
        $mock2
            ->expects($this->once())
            ->method('extract')
            ->with($object, $property)
            ->willReturn('baz');

        $this->assertSame('baz', $strategy->extract($object, $property));
    }

    public function testThrowWhenNoStrategySupporting()
    {
        $strategy = new DelegationStrategy(
            $mock1 = $this->createMock(ExtractionStrategy::class),
            $mock2 = $this->createMock(ExtractionStrategy::class)
        );
        $object = new \stdClass;
        $property = 'foo';
        $mock1
            ->expects($this->once())
            ->method('supports')
            ->with($object, $property)
            ->willReturn(false);
        $mock2
            ->expects($this->once())
            ->method('supports')
            ->with($object, $property)
            ->willReturn(false);
        $mock1
            ->expects($this->never())
            ->method('extract');
        $mock2
            ->expects($this->never())
            ->method('extract');

        $this->expectException(PropertyCannotBeExtracted::class);
        $this->expectExceptionMessage('Property "foo" cannot be extracted');

        $strategy->extract($object, $property);
    }
}
