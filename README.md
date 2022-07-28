## Custom Data

A package that minimize mixed arguments of functions or class methods

Whenever i see a function with argument `$data` i'm like 🤮, because i have to activate my prophetic side to guess which king of data is provided. so i decided to make this package so that you can start defining your arguments like

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

And that's all🤪😋, ==> Now go and build something beautiful, it's okay you can thanks me later, i understand that you are excited to install the package first😂
