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

And that's all🤪😋, ==> Now go and build something beautiful, it's okay you can thanks me later, i understand that you are excited to install the package first😂
