# How can you mock global functions with PHPUnit?

## Introduction

Sometimes I have to test global functions in legacy code. I know that it is not a good practice, but I have to do it. I have found a way to do it with PHPUnit. I will show you how to do it here.

## [The code](src/DateGetter.php)

I would like to test the `get_date()` function. It uses global functions (`time()`, `date()`). I would like to test it.

```php
<?php

namespace MyNamespace;

class DateGetter
{
    public function get_date($format = 'Y-m-d H:i:s', $timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = time();
        }

        return date($format, $timestamp);
    }
}
```

## The basic concept

From the version of 5.3 in PHP we have namespaces. We can use namespaces to organize our code. And we can use this possibility to test global functions. **We can only test global function calls when it called in namespace.**

When we call a function inside a namespace, PHP will search for the function in the current namespace. If it does not find it, it will search for it in the global namespace. We can use this to our advantage.

We can override the global function in the current namespace. We can do this with the `namespace` keyword. We can override the global function with a function with the same name in the current namespace.

If we call the `time()` function in the `MyNamespace` namespace, PHP will call the `time()` function in the `MyNamespace` namespace. So we can override the global function with a function with the same name in the `MyNamespace` namespace.

```php
<?php

namespace MyNamespace;

function time()
{
    return 1234567890;
}
```

## Go advanced

I would like to search a solution where I can set up the global function with the PHPUnit to make it more flexible and the calls can be testable.

I have found a solution. I have created helper classes in the same namespace.

Just for the full view, I will show you the how I override the global functions in the `MyNamespace` namespace.

0. The [Global fucntions override](tests/DateGetterTest.php#L55-L61)

```php
function time() {
    return call_user_func([GlobalFunctionsMocker::class, 'time']);
}

function date(...$args) {
    return call_user_func_array([GlobalFunctionsMocker::class, 'date'], $args);
}
````

1. The [Global Function Mocker](tests/DateGetterTest.php#L39-L53) class

The goal of this class is to provide a possibility to be callable from anywhere and forward the calls to the PHPUnit mock object.

```php
class GlobalFunctionsMocker
{
    /** @var GlobalFunctionMockPlaceholder */
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
```

2. The [Global Function Helper](tests/DateGetterTest.php#L28-L37) class

The goal of this class is to create a very simple class with the functions what we would like to mock. We can use this class to mock the global functions. I would like to use of the mock of this class to set it to the `GlobalFunctionsMocker::$mock` property.

```php
class GlobalFunctionMockPlaceholder
{
    public function time()
    {
    }

    public function date()
    {
    }
}
```

In this way we can mock the global functions with PHPUnit. The call stack seems like this for example:
1. Call the `time()` function in the `MyNamespace` namespace.
2. Call the `GlobalFunctionsMocker::time()` function.
3. Call the `GlobalFunctionsMocker::$mock->time()` function. So the `time()` function of the mocked `GlobalFunctionMockPlaceholder` class.

## [The test](tests/DateGetterTest.php#L7-L26)

The environment is ready. We can write the test. We can create a mock for global functions and to use that, we can set it to the `GlobalFunctionsMocker::$mock` property.

```php
class GlobalFunctionGetDateTest extends PHPUnit_Framework_TestCase
{
    public function testGetDate()
    {
        $GlobalFunctionMock = $this->createMock(GlobalFunctionMockPlaceholder::class);
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
```

## Summary

I hope it helps to understand the possibilities behind the namespaces with global functions. I hope it helps to test your legacy code.
