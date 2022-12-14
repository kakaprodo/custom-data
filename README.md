## Custom Data

A package that chase the delcaration of many arguments to a functions or class methods

Whenever i see a function with argument `($data1, $data2...)` i'm like 🤮, because i have to activate my prophetic side to guess which king of data is being provided. so i decided to make this package so that you can start defining your arguments like

```php
function handle(CreateUserData $data) {
    // do the magic
}
```

And if you like to decouple your code with small classes called `Action` , then this can work good for you, especially when you are writing your automated tests

## Installation

```sh
composer require kakaprodo/custom-data
```

## How to use

### Define a Data class

After the installation you will need to extends the abstract class: `Kakaprodo\CustomData\CustomData` on your ClassNameData:

```php
namespace App\Http\Data;

use Kakaprodo\CustomData\CustomData;

class CreateUserData extends CustomData
{
    protected function expectedProperties(): array
    {
        return [
        ];
    }

    public function boot()
    {
        // add your own validations
    }
}

```

As you can see, we have two important methods:

1. `expectedProperties`, is where you are going to define the property that should be provided when using Data Class.

2. `boot` : this will contain any logic you want to execute before the Data class can be returned to the place it's been called.

### Use your Data Class

To use your data class, the package provides a static method called `make`, this will receive an array of data and then will manage the package lifecycle .

```php
    $data = CreateUserData::make([
        'name' => 'kakaprodo',
        'email' => 'email@gmail.com',
        'age' => 30
    ]);

    $data->age;
```

### Validate incoming properties

Just add the expected properties in your `expectedProperties`, and add a suffix `?` for ooptional properties, remember the idea is to know which king of data is being used, so it's good to provide all the expected properties:

```php
    protected function expectedProperties(): array
    {
        return [
            'name'
            'email',
            'age?' // this is optional
        ];
    }
```

### Data type support

This package allows you to define data type on your expected properties like bellow:

```php
    protected function expectedProperties(): array
    {
        return [
            'name' => $this->dataType()->string()
            'price' => $this->dataType()->numeric()
            'user' => $this->dataType(\Customer\Path\User::class)
        ];
    }
```

You can also define a default value of an expected property

```php
    protected function expectedProperties(): array
    {
        return [
            'price?' => $this->dataType()->numeric(100)
            // Or
            'name?' => $this->dataType()->string()->default('kakaprodo')
        ];
    }
```

Here is the list of the supported data type so far:

-   string: `$this->dataType()->string()`
-   numeric: `$this->dataType()->numeric()`
-   bool : `$this->dataType()->bool()`

### Use your Data class inside an action

```php

class CreateUserAction
{
    public static function handle(CreateUserData $data)
    {
        // then do whatever you wanna do here
    }
}

```

## Support Custom Data Injection

For people who like to split their business logic into small classes called `action`, here is your chocolate,

```php
//Before

//defining action that use CustomData parameters

class MyAction
{
    public static function handle(MyActionData $data) {

    }
}


// call the action


MyAction::handle(MyActionData::make([
    'arg1' => value,
    'arg2' => value,
    'arg3' => value
    ...
]));

```

Can you imagine you have to call your `CustomData`(MyActionData) wherever you are using yoour actioon, that's too much🤮 🤮 🤮 🤮 . Now the good news is that, you can extends the `Kakaprodo\CustomData\Helpers\CustomActionBuilder` class to your action and then, it will do for you the injection magic of your customData using its `process` method. (What?😳, Noooo way😎!).

```php
//After

//defining action that use CustomData parameters

use Kakaprodo\CustomData\Helpers\CustomActionBuilder;

class MyAction extends CustomActionBuilder
{
    public static function handle(MyActionData $data) {

    }
}


// call the action without defining  MyActionData class

MyAction::process([
    'arg1' => value,
    'arg2' => value,
    'arg3' => value
    ...
]);

```

The `process` method can support also an existing CustomData object like:

```php
    $data = CreateUserData::make([
        'email' => 'email@gmail.com',
        'user' => new User()
    ]);

    MyAction::process($data);
```

## Support Dynamic handler method on class that extends CustomActionBuilder

You may need to extend the `CustomActionBuilder` on a class that has more than one method, yet you need to use CustomData
injection feature.To achieve that you need to use the static method `on` before calling the `process` method like this:

```php
MyAction::on($myHandlerMethod)->process([
    'arg1' => value,
    'arg2' => value,
    'arg3' => value
    ...
]);
```

## Support Multiple Data Type On property

Some times you may want to check multiple types on a given property, like let's say you want to check if a property is boolean(`true`) or numeric(`1`), this how you can do that

```php
    protected function expectedProperties(): array
    {
        return [
            'active' => $this->dataType()->bool()->orUseType('numeric')
        ];
    }
```

Here is the list of additional types you can use : `string`,`integer`,`float`,`bool`,`array`, `object`,`numeric`

## Support Custom Validation on Property

Some times you may wnat to define your logiic on how to validate a property, you achieve that by doing this way

```php
    protected function expectedProperties(): array
    {
        return [
           'age'  => $this->dataType(function ($value) {
               if (!is_numeric($value)) throw \Exception('how can you provide a such value🤪');
            })
        ];
    }

    // OR

    protected function expectedProperties(): array
    {
        return [
           'age'  => $this->dataType()->customValidator(function ($value) {
                if (!is_numeric($value)) throw \Exception('how can you provide a such value🤪');
            })
        ];
    }

```

The `$value` is the value of the given property, which is `age` in the above case

## Custom Data unique Key

In case you may want to generate a unique key for your custom data, you can use the `@dataKey`

```php
 $data = CreateUserData::make([
        'email' => 'email@gmail.com',
        'user' => 1
    ]);
 $data->dataKey(); // result: email=email@gmail.com@user=1
```

The key is generated based on the provided customData properties, so Inside the customData class, you can be able to
ignore the fields you don't want to appear in the generated key

```php
class CreateUserData extends CustomData
{
    protected function expectedProperties(): array
    {
        return [
            'email',
            'user'
        ];
    }

    protected function ignoreForKeyGenerator(): array
    {
        return ['user'];
    }
}
```

😘Personaly, i use this method in case i want to cache a request response found based on given customdata values

## Check property existance

Sometimes you may want to check if a given property was provided on the customData class, otherwise you throw an exception

```php

$data = CreateUserData::make([
        'email' => 'email@gmail.com',
        'user' => 1
    ]);

// here you can check if another field was set
$data->throwWhenFieldAbsent('other_option', 'your error message');
```

And that's all🤪😋, ==> Now go and build something beautiful, it's okay you can thanks me later, i understand that you are excited to install the package first😂
