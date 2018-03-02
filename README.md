# Gaslawork

This project is under heavy development. I'm still in the phase of investigating the most performant solutions.

## Setup

First we need to include your application into `composer.json` so that Composer can auto load your application classes:

```json
{
    "autoload": {
        "psr-4": {
            "Application\\": "application/",
        }
    }
}
```

TODO: composer commands.

And now we are going to create your index file. It is good practice to have the index file separated from your application code, let's create `public/index.php`:

```php
require '../vendor/autoload.php';

use Gaslawork\Routing\Routes;
use Gaslawork\Routing\Route;

$routes = (new Routes)
	->add(new Route("/:controller/:action/:id", "\Application\Controller\\"));

$app = new Gaslawork\App($routes);
// $app->base_url = "/";
$app->index_file = "index.php";

$app->run();
```

This is enough to get you going. This index file does not handle 404 or non-caught exceptions which might seem critical (and they are), and I'm going to cover that in later sections.

### base_url and index_file

These are two optional settings on the `App` object, and they are used to help Gaslawork determine the URI if the incoming requests.

* **base_url** is used if your application is not in the root of the domain. If you for example have your application under `https://example.com/foobar`, the `base_url` should be `"/foobar/"`.
* **index_file** is used when you have the index file in the URL, for example `https://example.com/index.php/foo/bar`. In that example the `index_file` should be `"index.php"`. It never hurts to add this setting even if you do not plan to have the index file in the URLs. This setting can be an aid when Gaslaworks tries to detect the URI.

### Apache

This is an example `.htaccess` file that you can put alongside your index file:

```
Options +FollowSymLinks -MultiViews

# Turning on URL rewriting
RewriteEngine On

# Base directory
RewriteBase /

# Denying access for hidden files
<Files .*>
	Order Deny,Allow
	Deny From All
</Files>

# Allow access to files and folders
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Rewriting URLs to index.php/*URI*
RewriteRule .* index.php/$0 [PT]
```

### Nginx

TODO

## Routing

Gaslawork uses dynamic routing. Here is an example using one single route:

```php
use Gaslawork\Routing\Routes;
use Gaslawork\Routing\Route;

$routes = (new Routes)
    ->add(new Route("/:controller/:action/:id", "\Application\Controller\\"));
```

In a perfect world this is the only route you will ever add.

The `Routes` object will hold all the routes, and we `add` a `Route` object to it.

### Deep dive into the internals

I'm now going to explain how the routing internals work. You do not _need_ to know this, but I find it comforting knowing the internals of the framework I'm using, and maybe you do too:

- Gaslawork internally calls the `findRoute()` of the routing (`Routes`) object.
- `Routes` iterates through all the routes added and calls `checkRoute()` on them. We have added the built-in `Route` in this example, but you are free to write your own route classes. I'll describe how to do that in a later section.
- `Routes` returns the route object back to Gaslawork when route's `checkRoute()` returns `true`
- The path to the controller (and in this case also the action) is fetched by calling `getController()` and `getAction()` of the route object.
- Gaslawork imports the controller and calls the action.

### Parameters (and special parameters)

In the example above we have put `/:controller/:action/:id` as the "target", and `\Application\Controller\\` as the "namespace prefix". Parts that begins with `:` is considered as "parameters", and there are three special parameters:

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
/                         | \Application\Controller\Index    | indexAction 
/foo                      | \Application\Controller\Foo      | indexAction 
/foo/bar                  | \Application\Controller\Foo      | barAction 

#### Action prefix and suffix

You may have noticed in the previous example that the action (method) names are suffixed with `Action`. This is because of security. For example, calling `index()` on the `Index` class is treated as calling the constructor.

You can change the suffix by using this method:

```php
$route->setActionSuffix("Hello"); // indexHello
```

And there is also a setting for adding a prefix:

```php
$route->setActionPrefix("action_"); // action_index
```

You can use a combination of both if you feel like it.

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

### Advanced routing

This is a fully working route:

`/hello/:action/world/:controller/foo/:id`

An example URL that matches this route is:

`/hello/abc/world/def/foo`

The controller will be `def` and the action will be `abc`.

### Why dynamic routing is used

Many other frameworks choose to map URLs to controllers but Gaslawork's routes are more performant when having a lot of URLs. PHP is executing the whole code every time, which means that the router's objects needs to be built from scratch each time, wasting resources on things that are never going to be used. After all, when visiting a page only one route is the correct one, and all the other ones are created but never used. Even when using cache the objects needs to be unserialized to be used, making it slower the more URLs you have.

### To write about

* Calling `__invoke()` on controller.
* :directory
* defaults
* required
* whitelist
* blacklist