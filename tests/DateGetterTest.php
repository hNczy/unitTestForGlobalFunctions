<?php

namespace MyNamespace;

use PHPUnit_Framework_TestCase;

class DateGetterTest extends PHPUnit_Framework_TestCase
{
    public function testGetDate()
    {
        $GlobalFunctionMock = $this->createMock(GlobalFunctionsMockPlaceholder::class);
        $GlobalFunctionMock->expects($this->once())
            ->method('time')
            ->willReturn(1420070400);
        $GlobalFunctionMock->expects($this->once())
            ->method('date')
            ->with('Y-m-d H:i:s', 1420070400)
            ->willReturn('2015-01-01 00:00:00');
        GlobalFunctionsMocker::$mock = $GlobalFunctionMock;

        $this->assertEquals(
            '2015-01-01 00:00:00',
            (new DateGetter())->get_date()
        );
    }
}

class GlobalFunctionsMockPlaceholder
{
    public function time()
    {
    }

    public function date()
    {
    }
}

class GlobalFunctionsMocker
{
    /** @var GlobalFunctionsMockPlaceholder */
    static $mock;

    public static function time()
    {
        return call_user_func([self::$mock, 'time']);
    }

    public static function date(...$args)
    {
        return call_user_func_array([self::$mock, 'date'], $args);
    }
}

function time() {
    return call_user_func([GlobalFunctionsMocker::class, 'time']);
}

function date(...$args) {
    return call_user_func_array([GlobalFunctionsMocker::class, 'date'], $args);
}
