# Custom Data

## [Official Documentation](https://yupidoc.com/projects/customdata/preview)

A Laravel package that wraps function arguments together into a single CustomData class allowing separate processing and validation for each argument.

```php
class CreateUserData extends CustomData
{
    protected function expectedProperties(): array
    {
        return [
            'name' => $this->dataType()->string(),
            'email' => $this->dataType()->string(),
            'password' => $this->dataType()->string(),
            'age?' => $this->dataType()->string(),
            'sexe' => $this->dataType()->inArray(['M','F'])
        ];
    }
}
```

And then call it this way:

```php
CreateUserData::make([
    'name' => 'kakaprodo',
    'email' => 'example@gmail.com',
    'password' => 'is_unique_pass',
    'sexe' => 'M'
]);
```

And if you like to decouple your code with small classes called Action , then you are at the right place:

```php
class CreateUserAction extends CustomActionBuilder
{
   public function handle(CreateUserData $data)
   {
       return $data->onlyValidated();
   }
}
```

And then we call our action this way:

```php
CreateUserAction::process([
    'name' => 'kakaprodo',
    'email' => 'example@gmail.com',
    'password' => 'is_unique_pass',
    'sexe' => 'M'
]);
```

## Features

-   Combine several function arguments into one class called CustomData
-   Validate each argument a little bit the way TypeScript does it in Javascript
-   Support the definition of Laravel FormValidation rules
-   Support Action classes in which CustomData can be injected
-   Support The Ability to Queue Action class
-   Support helper command to generate Action and CustomData classes

You can find here the [Official Documentation](https://yupidoc.com/projects/customdata/preview)
