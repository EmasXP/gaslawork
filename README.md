# Gaslawork

This project is under heavy development. I'm still in the phase of investigating the most performant solutions.

## Routing

Gaslawork uses dynamic routing. Here is an example using one single route:

```php
use Gaslawork\Routing\Route;
use Gaslawork\Routing\Routes;

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
/                         | \Application\Controller\Index    | action_index
/foo                      | \Application\Controller\Foo      | action_index
/foo/bar                  | \Application\Controller\Foo      | action_bar

#### Action prefix and suffix

You may have noticed in the previous example that the action (method) names has `action_` prefixed. This is because of security. For example, calling `index()` on the `Index` class is treated as calling the constructor.

You can change the prefix by this method:

```php
$route->setActionPrefix("hello_"); // hello_index
```

And there is also a setting for adding a suffix:

```php
$route->setActionSuffix("_action"); // index_action
```

#### Fetching parameters

Parameters can be fetched by the controller or via the request object's `getParam()` method.

```php
class Index extends \Gaslawork\Controller {

    public function action_index()
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