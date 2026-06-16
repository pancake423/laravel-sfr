# Static Form Requests for Laravel

Streamline your API request validation and ensure type safety.

Laravel form requests are powerful, but getting back validated data in an array is messy.
You end up with array key strings in your controllers, which are vague and error prone.

What we want instead are nice objects with explicitly defined properties. Now your IDE knows
what fields exist on your controllers, and what types they are. Happy IDE = less silly bugs = 
happy developer.

Note that this might not be a suitable replacement for all FormRequests, as it is just designed
to streamline basic use cases. It is best suited for simple API requests.

## How To Use

This project takes advantage of PHP8's Attributes to achieve simple, minimalistic code. Anyone who
has worked with Livewire will find the syntax familiar.

For example, here's what a login request might look like with SimpleFormRequest:

```php
<?php
namespace App\Http\Requests;

use Pancake423\StaticFormRequest\StaticFormRequest;
use Pancake423\StaticFormRequest\Validate;
use Pancake423\StaticFormRequest\Message;

class LoginRequest extends StaticFormRequest 
{
    public function authorize() 
    {
        // define the authorize method like you would on a normal FormRequest.
        // note that if you need to access the actual form request (ie. to take)
        // advantage of route-model binding, you'll have to use
        // $this->form() instead of just $this.
        return true;
    }
    
    #[Validate("required|string|min:4|max:32")]
    #[Message([
        "username.min" => "Username too short!",
        "username.max" => "Username too long!",
    ])]
    public string $username;

    // It's also acceptable to pass validation rules as an array.
    #[Validate(["required", "string", "min:8"])] 
    public string $password;

}
```

and here's what the corresponding controller might look like:

```php
<?php
namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;

class AuthController extends Controller {
    public function login(LoginRequest $request)
    {
        // just type-hint the LoginRequest and let Laravel magic
        // take care of the rest!

        // the data is already validated by the time it gets to your controller.
        // if you need the data as an array, ie. to pass to an Eloquent model,
        // use $request->toArray().
        dd($request->username, $request->password, $request->toArray());
    }
}
```

## Caveats

Can't handle all use cases for FormRequests (yet?). Nested fields won't work, and array data might not behave well.
Additionally, if you use extra validation steps (`prepareForValidation()` or similar), those won't work. Other keys
defined on a FormRequest, like $redirect, also won't work. But, it makes the easy cases really pretty :)

## How To Install

```bash
composer require pancake423/laravel-sfr
```

Note that this library requires Laravel. 
If you're not using Laravel, it will not work!
