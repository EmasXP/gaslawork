# Gaslawork

This project is under heavy development. I'm still in the phase of investigating the most performant solutions.

## Hello world

```php
use \Gaslawork\Routing\Routes;
use \Gaslawork\Routing\Route;

$routes = (new Routes)
    ->add(new Route("/:controller/:action/:id", "\Controller\\"));

$app = new \Gaslawork\App($routes);

$app->run();
```

```php
namespace Controller;

class Index extends \Gaslawork\Controller {

    public function indexAction()
    {
        print "Hello, world!";
    }

}
```

## Documentation

You can read the full documentation on <https://emasxp.github.io/gaslawork/>.