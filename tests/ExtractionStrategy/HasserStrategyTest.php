<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\{
    ExtractionStrategy\HasserStrategy,
    ExtractionStrategy,
    Exception\LogicException,
};
use Fixtures\Innmind\Reflection\Foo;
use PHPUnit\Framework\TestCase;

class HasserStrategyTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            ExtractionStrategy::class,
            new HasserStrategy
        );
    }

    public function testSupports()
    {
        $o = new class {
            public $c;

            public function hasA()
            {
            }

            public function hasB($b)
            {
            }

            public function hasSomeLongProperty()
            {
            }

            private function hasFoo()
            {
            }

            protected function hasBar()
            {
            }
        };

        $s = new HasserStrategy;

        $this->assertTrue($s->supports($o, 'a'));
        $this->assertTrue($s->supports($o, 'some_long_property'));
        $this->assertFalse($s->supports($o, 'b'));
        $this->assertFalse($s->supports($o, 'c'));
        $this->assertFalse($s->supports($o, 'foo'));
        $this->assertFalse($s->supports($o, 'bar'));
    }

    public function testThrowWhenExtractingUnsuppportedProperty()
    {
        $o = new \stdClass;
        $s = new HasserStrategy;

        $this->expectException(LogicException::class);

        $s->extract($o, 'a');
    }

    public function testExtract()
    {
        $o = new class {
            public function hasA()
            {
                return true;
            }

            public function hasSomeLongProperty()
            {
                return true;
            }
        };
        $s = new HasserStrategy;

        $this->assertTrue($s->extract($o, 'a'));
        $this->assertTrue($s->extract($o, 'some_long_property'));
    }

    public function testExtractWithInheritedMethod()
    {
        $strategy = new HasserStrategy;
        $object = new class extends Foo {};

        $this->assertTrue($strategy->extract($object, 'someProperty'));
    }
}
