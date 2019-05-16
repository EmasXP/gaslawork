# Gaslawork

This project is under heavy development. I'm still in the phase of investigating the most performant solutions.

## Setup

First we need to include your application classes into `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "": "classes/",
        }
    }
}
```

This enabled auto loading for all the classes in the `classes` folder. This example is without a namespace prefix, but you are free to do as you like.

TODO: composer commands.

And now we are going to create your index file. It is good practice to have the index file separated from your application code, let's create `public/index.php`:

```php
require '../vendor/autoload.php';

use Gaslawork\Routing\Routes;
use Gaslawork\Routing\Route;

$routes = (new Routes)
    ->add(new Route("/:controller/:action/:id", "\Controller\\"));

$app = new Gaslawork\App($routes);
// $app->base_url = "/";
// $app->index_file = "index.php";

$app->run();
```

This is enough to get you going. This index file does not handle 404 or non-caught exceptions which might seem critical (and they are), and I'm going to cover that in later sections.

### base_url and index_file

These are two optional settings on the `App` object, and they are used to help Gaslawork determine the URI if the incoming requests.

* **base_url** is used if your application is not in the root of the domain. If you for example have your application under `https://example.com/foobar`, the `base_url` should be `"/foobar/"`.
* **index_file** is used when you have the index file in the URL, for example `https://example.com/index.php/foo/bar`. In that example the `index_file` should be `"index.php"`. It never hurts to add this setting even if you do not plan to have the index file in the URLs. This setting can be an aid when Gaslaworks tries to detect the URI. Gaslawork tries to figure out the name of the index file if the setting is left empty (if needed).

### Setting up the web server

I'm explaining how to set up Apache and Nginx in a later section called "Web servers".

## Routing

Gaslawork uses dynamic routing. Here is an example using one single route:

```php
use Gaslawork\Routing\Routes;
use Gaslawork\Routing\Route;

$routes = (new Routes)
    ->add(new Route("/:controller/:action/:id", "\Controller\\"));
```

In a perfect world this is the only route you will ever add.

The `Routes` object will hold all the routes, and we `add` a `Route` object to it.

### Deep dive into the internals

I'm now going to explain how the routing internals work. You do not _need_ to know this, but I find it comforting knowing the internals of the framework I'm using, and maybe you do too:

- Gaslawork internally calls the `findRoute()` of the routing (`Routes`) object.
- `Routes` iterates through all the routes added and calls `checkRoute()` on them. We have added the built-in `Route` in this example, but you are free to write your own route classes. I'll describe how to do that in a later section.
- `Routes` returns the route object back to Gaslawork when route's `checkRoute()` returns `true`
- The path to the controller (and in this case also the action) is fetched by calling `getController()` and `getAction()` of the route object.
- Gaslawork creates an object of the controller and calls the action.

### Parameters (and special parameters)

In the example above we have put `/:controller/:action/:id` as the "target", and `\Controller\\` as the "namespace prefix". Parts that begins with `:` is considered as "parameters", and there are three special parameters:

- :directory
- :controller
- :action

These parameters decide which controller and action should be executed. These parameters has default values:

- :controller - `index`
- :action - `index`

The defaults are going to be used if a parameter is not set by the URL.

The namespace prefix is where Gaslawork should be looking for the controllers.

Here are some examples of URL's - using the route we defined in the example above:

URL                       | Controller (Class)               | Action (Method)
--                        | --                               | --
/                         | \Controller\Index    | indexAction
/foo                      | \Controller\Foo      | indexAction
/foo/bar                  | \Controller\Foo      | barAction

You may have noticed in the previous example that the action (method) names are suffixed with `Action`. This is because of security. For example, calling `index()` on the `Index` class is treated as calling the constructor.

#### Fetching parameters

Parameters can be fetched by the controller or via the request object's `getParam()` method.

```php
class Index extends \Gaslawork\Controller {

    public function indexAction()
    {
        print $this->getParam("id");
        // Or:
        print \Gaslawork\Request::current()->getParam("id");
    }

}
```

#### Default parameters

All parameters can have default values.

```php
$routes = (new Routes)->add(
    (new Route("/:controller/:action/:id", "\Controller\\"))
        ->setDefaults(array(
            "controller" => "welcome",
            "action" => "hello",
        ))
);
```

The example above will have the default `controller` to be `welcome` and the default `action` to be `hello`. That means that the URI `/` will match to the controller `\Controller\Welcome\` and the action `helloAction` will be called.

You can set defaults for all parameters:

```php
$routes = (new Routes)->add(
    new Route("/:controller/:action/:id", "\Controller\\"))
        ->setDefaults(array(
            "controller" => "welcome",
            "action" => "hello",
            "id" => "123",
        ))
);
```

This will make the `id` parameter to be `123` until the parameter is set in the URI.

Since the `setDefaults()` method overwrites all preexisting defaults, you can remove the default of `controller` and `action`. Be careful though, a route that is missing the controller is treated as invalid. A missing action will call the `__invoke()` method of the controller. This is of course perfectly valid and some developers prefer it.

You can also set a default for a parameter that is not in the "target":

```php
$routes = (new Routes)->add(
    (new Route("/:action", "\Controller\\"))
        ->setDefaults(array(
            "controller" => "hello",
            "action" => "index",
        ))
);
```

In this example the controller will always be `\Controller\Hello`. You can use this idea to totally remove actions from your application (or a specific route), and only use controllers:

```php
$routes = (new Routes)->add(
    (new Route("/:controller", "\Controller\\"))
        ->setDefaults(array(
            "controller" => "index",
        ))
);
```

This route does not have an action, and can never have, so the `__invoke()` method of the controller will always be called.

#### White listing parameters

```php
$routes = (new Routes)
    ->add(
        (new Route("/:controller/:action/:id", "\Controller\Special\\"))
            ->setWhitelist(array(
                "controller" => array("foo"),
            ))
    )
    ->add(new Route("/:controller/:action/:id", "\Controller\\"));
```

We are creating a `Rotue` object and call `setWhitelist()` on it. We pass a dictionary to that method where the key is the name of the parameter. The value of the dictionary is an array of allowed values of the parameter. All other values than is specified here will not match the route.

The example above will call the controller `\Controller\Special\Foo` when the `controller` parameter is `foo`.

You can white list all parameters, not just the special onces.

#### Black listing parameters

```php
$routes = (new Routes)
    ->add(
        (new Route("/:controller/:action/:id", "\Controller\\"))
            ->setBlacklist(array(
                "controller" => array("foo"),
            ))
    )
    ->add(new Route("/:controller/:action/:id", "\Controller\Special\\"));
```

This time we call the `setBlacklist()` method on the `Route` object. We pass a dictionary to that method where the key is the name of the parameter and the value is an array of non-allowed parameter values.

In this example above the controller `\Controller\Bar` will be called if the `controller` parameter is `bar`, but the controller `\Controller\Special\Foo` will be called when the `controller` parameter is `foo`.

#### Setting parameters as required

```php
$routes = (new Routes)
    ->add(
        (new Route("/:controller/:action/:id", "\Controller\WithId\\"))
            ->setRequired(array("id"))
    )
    ->add(new Route("/:controller/:action/:id", "\Controller\\"));
```

The example above sets the `id` parameter to be required. So the URI `/Hello/World/123` will match the first route and the controller `\Controller\WithId\Hello\` will be used.

### Advanced routing

This is a fully working route:

`/hello/:action/world/:controller/foo/:id`

An example URL that matches this route is:

`/hello/abc/world/def/foo`

The controller will be `def` and the action will be `abc`.

### Why dynamic routing is used

Many other frameworks choose to map URLs to controllers but Gaslawork's routes are more performant when having a lot of URLs. PHP is executing the whole code every time, which means that the router's objects needs to be built from scratch each time, wasting resources on things that are never going to be used. After all, when visiting a page only one route is the correct one, and all the other ones are created but never used. Even when using cache the objects needs to be unserialized to be used, making it slower the more URLs you have.

### To write about

* :directory

## Controllers and Actions

### Action prefix and suffix

Action (method) names are by default suffixed with `Action`.  But you can change the suffix by setting the `action_suffix` on the `App` object:

```php
$app->action_suffix = "Hello"; // indexHello
```

And there is also a setting for adding a prefix:

```php
$app->action_prefix = "action_"; // action_indexAction
```

In the example above both the prefix and suffix was added. To have only suffix, we need to write like this:

```php
// action_index:
$app->action_prefix = "action_";
$app->action_suffix = "";
```


## Web servers

### Apache

This is an example `.htaccess` file that you can put alongside your index file:

```
Options +FollowSymLinks -MultiViews

# Turning on URL rewriting
RewriteEngine On

# Base folder
RewriteBase /

# Denying access for hidden files
<Files .*>
	Order Deny,Allow
	Deny From All
</Files>

# Allowing direct access to files and folders
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Rewriting URLs to index.php/*URI*
RewriteRule .* index.php/$0 [PT]
```

### Nginx

TODO
