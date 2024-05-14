![laravel-eupago-repo-banner](https://pbs.twimg.com/profile_banners/593785558/1671194657/1500x500)

# Laravel EuPago

A Laravel package for making payments through the EuPago API based in CodeTech's laravel-eupago package. Well Done CodeTech's Team.

[![Latest version](https://img.shields.io/github/release/digitaldev-lx/laravel-eupago?style=flat-square)](https://github.com/digitaldev-lx/laravel-eupago/releases)
[![GitHub license](https://img.shields.io/github/license/digitaldev-lx/laravel-eupago?style=flat-square)](https://github.com/digitaldev-lx/laravel-eupago/blob/master/LICENSE)

## Installation

Install the PHP dependency:
```bash
composer require digitaldev-lx/laravel-eupago
```

Publish the migration
```
php artisan vendor:publish --provider=DigitaldevLx\\LaravelEupago\\Providers\\EuPagoServiceProvider --tag=migrations
```

Run the migration
```
php artisan migrate
```

Publish the configuration file (optional)
```
php artisan vendor:publish --provider=DigitaldevLx\\LaravelEupago\\Providers\\EuPagoServiceProvider --tag=config
```

Publish the translations files (optional)
```
php artisan vendor:publish --provider=DigitaldevLx\\LaravelEupago\\Providers\\EuPagoServiceProvider --tag=translations
```


## Configurations

### Environment

There are two environments available for you to use: "test" and "prod". As you may have guessed,
you can use the "test" environment during the development stage of your application. Switch to "prod"
environment when your application is ready for production.


### MB References

#### Usage

For creating a MB reference, take the following example:
```
use DigitaldevLx\LaravelEupago\MB\MB;

$order = Order::find(1);

$mb = new MB(
    $order->value,
    $order->id,
    $order->date,
    $order->payment_limit_date,
    $order->value,
    $order->value,
    0 // allows duplicated payments
);

try {
    // Make the request to EUPago's API
    $mbReferenceData = $mb->create();

    if ($mb->hasErrors()) {
        // handle errors
    }
    
    // Make the request to EUPago's API
    $order->mbReferences()->create($mbReferenceData);
} catch (\Exception $e) {
    // handle exception
}
```

`$referenceData` will contain all the information about the payment: 
```
[
    'success' => true,
    'state' => 0,
    'response' => "OK",
    'reference' => "000001236",
    'value' => "3.00000",
]
```

Use the trait on the models for which you want to generate MB references:

```

use DigitaldevLx\LaravelEupago\Traits\Mbable;

class Order extends Model
{
    use Mbable;

```

Retrieve the MB references:

```
$order = Order::find(1);

$mbReferences = $order->mbReferences;
```

#### Callback

The package already handles the callback, updating the payment reference state and triggering an `MBWayReferencePaid` event.

```
GET

/eupago/mb/callback
```

####Params

| Name          | Type      |
|---------------|:---------:|
| valor         | float     |
| canal         | string    |
| referencia    | string    |
| transacao     | string    |
| identificador | integer   |
| mp            | string    |
| chave_api     | string    |
| data          | date time |
| entidade      | string    |
| comissao      | float     |
| local         | string    |


### MB Way References

#### Usage

Use the trait on the models for which you want to generate MB Way references:

```

use DigitaldevLx\LaravelEupago\Traits\Mbwayable;

class Order extends Model
{
    use Mbwayable;

```

Retrieve the MB Way references:

```
$order = Order::find(1);

$mbwayReferences = $order->mbwayReferences;
```

#### Callback

The package already handles the callback, updating the payment reference state and triggering an `MBWayReferencePaid` event.

```
GET

/eupago/mbway/callback
```

####Params

| Name          | Type      |
|---------------|:---------:|
| valor         | float     |
| canal         | string    |
| referencia    | string    |
| transacao     | string    |
| identificador | integer   |
| mp            | string    |
| chave_api     | string    |
| data          | date time |
| entidade      | string    |
| comissao      | float     |
| local         | string    |



---


## License

**digitaldev-lx/laravel-eupago** is open-sourced software licensed under the [MIT license](https://github.com/CodeTechAgency/laravel-eupago/blob/master/LICENSE).


## About DigitalDev

[DigitalDev](https://www.digitaldev.pt) is a web development agency based on Lisbon, Portugal. We specialize in Laravel, Livewire, and Tailwind CSS.
[Codeboys](https://www.codeboys.pt) is our special partner and we work together to deliver the best solutions for our clients.

