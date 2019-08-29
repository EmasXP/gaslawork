---
title: Controllers
permalink: /controllers/

---
# Controllers

## Action prefix and suffix

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