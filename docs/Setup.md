---
title: Setup
permalink: /setup/

---
# Setup

## Composer

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

This enabled auto loading for all the classes in the `classes` folder. This example is without a namespace prefix, but you are free to do as you like. Maybe you want the prefix to be "App" or the name of your application.

TODO: composer commands.

## index.php

And now we are going to create your `index.php` file:

```php
require "vendor/autoload.php";

use Gaslawork\Routing\Router;
use Gaslawork\Routing\Route;
use Spiral\RoadRunner;
use Nyholm\Psr7;

$routes = (new Router)
    ->add(
        (new Route("/:action/:id", Controller\Index::class, "{action}Action"))
            ->setDefaults([
                "action" => "index",
            ])
    );

$app = new \Gaslawork\App($routes);

$worker = RoadRunner\Worker::create();
$psrFactory = new Psr7\Factory\Psr17Factory();

$worker = new RoadRunner\Http\PSR7Worker($worker, $psrFactory, $psrFactory, $psrFactory);

while ($req = $worker->waitRequest()) {
    try {
        $rsp = $app->executeRequest($req);
        $worker->respond($rsp);
    } catch (\Throwable $e) {
        $worker->respond(new Psr7\Response(500, [], "Something Went Wrong!"));
    }
}
```

This is enough to get you going. This index file does not handle uncaught exceptions which might seem critical, but I'm going to cover that in later sections.

This example is only adding one route. Read more about routing in the [Routing]({{ site.baseurl }}/routing) document.
