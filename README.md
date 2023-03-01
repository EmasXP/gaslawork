# Gaslawork

## Project status

This is my hobby project. Like a pet I give far too little attention. Sometimes I get a bad conscience after not looking after it for a year. 

At this point - and maybe forever - one shall not look at this project as very serious. It is more like a playground for me. I keep trying out ideas, and then I get more bad conscience because I don't maintain the test suite.

## Project description

I just want to make a really fast framework. At first I took inspiration from [Slim](https://www.slimframework.com/), but now I take more inspiration from [Spiral](https://spiral.dev/). Focus is on speed, without forgetting to be user friendly.

## Hello world

```php
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
        $psr7->respond(new Psr7\Response(500, [], "Something Went Wrong!"));
    }
}
```

```php
namespace Controller;

use Nyholm\Psr7\Response;

class Index {

    public function indexAction(): Response
    {
        $response = new Response();
        $response->getBody()->write("Hello, world!");
        return $response;
    }

}
```

This example uses [RoadRunner](https://roadrunner.dev/), but any PSR17 compatible application will work.

Add a `.rr.yaml` that looks something like this:

```yaml
server:
  command: "php index.php"

http:
  address: 0.0.0.0:8080
  pool:
    num_workers: 4
```

And you can now start the server:

```shell
./rr serve
```

## Documentation

You can read the full documentation on <https://emasxp.github.io/gaslawork/>.