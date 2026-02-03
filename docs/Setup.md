---
title: Setup
permalink: /setup/

---
# Setup

## Composer and application classes

Follow the [instructions to install Composer](https://getcomposer.org/download/) first.

Then we need to include the application classes into `composer.json`:

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

TODO: composer command to install Gaslawork.

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

## RoadRunner

The example above is using RoadRunner. Follow the [install instructions on the RoadRunner web site](https://roadrunner.dev/docs/intro-install/).

We also need to create a `.rr.yaml` file. Something like this:

```yaml
server:
  command: "php index.php"

http:
  address: 0.0.0.0:8080
  middleware: [ "static" ]
  pool:
    num_workers: 4
  static:
    dir: "public"
```

This is just an example configuration. Check out [the RoadRunner configuration documentation](https://roadrunner.dev/docs/intro-config) for more information.

This example uses the folder "public" for static files. Maybe you are not interested in serving static files from RoadRunner, or maybe you want to use a different folder, or something else. Configure RoadRunner for your needs.

Then it's just a matter of starting the RoadRunner server:

```shell
./rr serve
```

## Workerman

This example shows how to run Gaslawork under Workerman.

First we need to install Workerman and Nyholm/Psr7:

```shell
composer require workerman/workerman nyholm/psr7
```

And then we'll modify the `index.php` example:

```php
require "vendor/autoload.php";

use Gaslawork\Routing\Router;
use Gaslawork\Routing\Route;
use Workerman\Worker;
use Workerman\Protocols\Http\Response;
use Nyholm\Psr7;

$routes = (new Router)
    ->add(
        (new Route("/:action/:id", Controller\Index::class, "{action}Action"))
            ->setDefaults([
                "action" => "index",
            ])
    );

$app = new \Gaslawork\App($routes);

$http_worker = new Worker("http://0.0.0.0:8080");
$http_worker->count = 4;

$psr17Factory = new Psr7\Factory\Psr17Factory();

$http_worker->onMessage = function (
    $connection,
    $request
) use (
    $app,
    $psr17Factory
) {
	$psr_request = $psr17Factory->createRequest(
        $request->method(),
        $request->uri()
    )
        ->withBody(
            $psr17Factory->createStream($request->rawBody())
        );

    $headers = $request->header();
    foreach ($headers as $name => $value) {
        $psr_request = $psr_request->withHeader($name, $value);
    }

    try {
        $psr_response = $app->executeRequest($psr_request);
    } catch (\Throwable $e) {
        $psr_response = new Psr7\Response(500, [], "Something Went Wrong!")
    }

    $response = new Response(
        $psr_response->getStatusCode(),
        $psr_response->getHeaders(),
        $psr_response->getBody()->__toString()
    );

    if ($request->header("connection") == "keep-alive") {
        $connection->send($response);
    } else {
        $connection->close($response);
    }
};

Worker::runAll();
```

This example converts the Workerman request into a Nyholm PSR request, and the PSR response into a Workerman response. Take this example with a grain of salt, as I don't know for certain that this works for all scenarios.

Then start the application:

```shell
php index.php start
```

