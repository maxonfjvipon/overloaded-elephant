# overloaded-elephant

Small trait that allows you to work with different types of variables in php (php 7+ needed)

Wide usage of the trait you can find in [ElegantElephant](https://github.com/maxonfjvipon/ElegantElephant)

## Why did I make it?

I wanted to have an ability to make classes that are able to manage their arguments of many different but strict types or Classes. Just look at this class from [ElegantElephant](https://github.com/maxonfjvipon/ElegantElephant):

```php
final class EqualityOf implements Logical
{
    use Overloadable;

    /**
     * @var string|int|float|Text|array|Arrayable|Logical|Numerable
     */
    private string|int|float|Text|array|Arrayable|Logical|Numerable $arg1;

    /**
     * @var string|int|float|Text|array|Arrayable|Logical|Numerable
     */
    private string|int|float|Text|array|Arrayable|Logical|Numerable $arg2;

    /**
     * Ctor.
     * @param string|int|float|Text|array|Arrayable|Logical|Numerable $arg1
     * @param string|int|float|Text|array|Arrayable|Logical|Numerable $arg2
     */
    public function __construct(
        string|int|float|Text|array|Arrayable|Logical|Numerable $arg1,
        string|int|float|Text|array|Arrayable|Logical|Numerable $arg2
    ) {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }


    /**
     * @inheritDoc
     */
    public function asBool(): bool
    {
        $operands = $this->overload([$this->arg1, $this->arg2], [[
            'string',
            'integer',
            'double',
            'array',
            Text::class         => fn(Text $txt) => $txt->asString(),
            Arrayable::class    => fn(Arrayable $arr) => $arr->asArray(),
            Logical::class      => fn(Logical $logical) => $logical->asBool(),
            Numerable::class    => fn(Numerable $num) => $num->asNumber()
        ]]);
        return $operands[0] === $operands[1];
    }
}
```

This class can work with many different types or Classes. And he knows what method of class should be called so object become comparable.  
For example, if the argument is type of `Arrayable` then we need to do `$arr->asArray()` and we get comparable `array`. And I can use this one class with many different arguments and can not to think about the types.  

```php
(new EqualityOf([1, 2, 3], new ArrayableOf([1, 2, 3]))->asBool(); // comparing arrays
(new EqualityOf(11, new SomeClassImplementsNumerable())->asBool(); // comparing numbers
(new EqualityOf("Hello, world!", new TxtJoined("Hello", ", ", "world", "!")))->asBool(); // comparing string
...
```

## How to use it

1. Make sure you do:
```php
use Overloadable;
```
2. Call `$this->overload()`. Method accepts 2 arguments: array of your parameter you want to overload and array of rules.  
3. Every rule must be an array. 
4. Every rule applies to the argument in strict order. If you have 2 arguments but 1 rule, this rule will be applied to all arguments
5. If you want to not apply rule to the first argument but to the second (or to argument with specific key) you may use `key => rule` structure:
```php
$this->overload([$arg1, 'key' => $arg2], ['key' => ['integer', 'float', ...]]) // here rule will be not applied to $arg1
```
6. Every element inside the rule array may be either string of the type like `integer`, `double`, `SomeClass::class` or `type => action` structure
```php
$this->overload([$arg1], [[
  'integer',
  SomeClass::class => fn() => 
]])
```
8. Action is a callback with one argument
