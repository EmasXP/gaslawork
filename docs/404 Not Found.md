---
title: 404 Not Found
permalink: /404-not-found/

---
# 404 Not Found

Gaslawork throws an `\Gaslawork\Exception\NotFoundException` exception that is catched internally by Gaslawork. This is the only exception that catched internally, and the reason for that is that Gaslawork want to show a proper 404 page. 404 can for example happen when a route for the URL does not exist, or the controller does not exist.

You can define your own 404 handler by defining a `notFoundHandler` in your [Dependency container]({{ site.baseurl }}/dependency-container):

```php
$container = (new \Gaslawork\Container)
    ->set("notFoundHandler", function($c){
        return function(\Gaslawork\Exception\NotFoundException $e)
        {
            \Gaslawork\Response::status(404);

            print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 3.2 Final//EN\">\n";
            print "<title>404 Not Found</title>\n";
            print "<h1>Oh, bummer!</h1>\n";
            print "<p>The requested URL ";
            $uri = $e->getUri();
            if ( ! empty($uri))
            {
                print "<i>".htmlspecialchars($uri)."</i> ";
            }
            print "was not found on the server.</p>";    
        };
    });

$app = new \Gaslawork\App($routes, $container);
```

You can of course render a view, or do whatever you like here. 

You don't need to use Gaslawork's built in Dependency container of course. 

The important thing to note here is that Gaslawork expects `notFoundHandler` to be a callable that takes a `\Gaslawork\Exception\NotFoundException` as it's only parameter.

Another example is to separate the handler into a class:

```php
class MyNotFoundHandler {

	public function __invoke(\Gaslawork\Exception\NotFoundException $e)
	{
            \Gaslawork\Response::status(404);

            print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 3.2 Final//EN\">\n";
            print "<title>404 Not Found</title>\n";
            print "<h1>Argh!</h1>\n";
            print "<p>The requested URL ";
            $uri = $e->getUri();
            if ( ! empty($uri))
            {
                print "<i>".htmlspecialchars($uri)."</i> ";
            }
            print "was not found on the server.</p>";
	}

}
```

```php
$container = (new \Gaslawork\Container)
    ->set("notFoundHandler", function($c){
        return new MyNotFoundHandler;
    });

$app = new \Gaslawork\App($routes, $container);
```

