---
title: Setup
permalink: /setup/

---
# Setup

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

use \Gaslawork\Routing\Router;
use \Gaslawork\Routing\Route;

$routes = (new Router)
    ->add(
        (new Route("/:controller/:action/:id", "\\Controller\\{+controller}", "{action}Action"))
            ->setDefaults([
                "controller" => "Index",
                "action" => "index",
            ])
    );

$app = new \Gaslawork\App($routes);
// $app->base_url = "/";
// $app->index_file = "index.php";

$app->run();
```

This is enough to get you going. This index file does not handle 404 or non-caught exceptions which might seem critical (and they are), and I'm going to cover that in later sections.

## base_url and index_file

These are two optional settings on the `App` object, and they are used to help Gaslawork determine the URI if the incoming requests.

* **base_url** is used if your application is not in the root of the domain. If you for example have your application under `https://example.com/foobar`, the `base_url` should be `"/foobar/"`.
* **index_file** is used when you have the index file in the URL, for example `https://example.com/index.php/foo/bar`. In that example the `index_file` should be `"index.php"`. It never hurts to add this setting even if you do not plan to have the index file in the URLs. This setting can be an aid when Gaslaworks tries to detect the URI. Gaslawork tries to figure out the name of the index file if the setting is left empty (if needed).

## Setting up the web server

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