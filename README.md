Sid\Phalcon\BoundModels
=======================

Automatically get models based on dispatcher parameters within the Phalcon framework.



## Installing ##

Install using Composer:

```json
{
	"require": {
		"sidroberts/phalcon-boundmodels": "dev-master"
	}
}
```



## Example ##

### DI ###

You can decide where the parameters come from using the `setParamSource()` method.

* `$boundModels->setParamSource(\Sid\Phalcon\BoundModels\Manager::DISPATCHER);` - `dispatcher->getParam()` (default)
* `$boundModels->setParamSource(\Sid\Phalcon\BoundModels\Manager::REQUEST_GET);` - `request->getQuery()`
* `$boundModels->setParamSource(\Sid\Phalcon\BoundModels\Manager::REQUEST_POST);` - `request->getPost()`

Alternatively, you can set custom params using the `setCustomParamSource()` method (eg. `$boundModels->setCustomParamSource(['param1' => 'abc', 'param2' => 'def', 'param3' => 'ghi']);`).

```php
$di->set(
	"boundModels",
	function () {
		$boundModels = new \Sid\Phalcon\BoundModels\Manager();
		
		return $boundModels;
	},
	true
);
```

### Model ###

```php
namespace Sid\Models;

class Posts extends \Phalcon\Mvc\Model
{
    public $categorySlug;
    public $postSlug;

    // ...
}
```

### Controller ###

There are 3 important methods: `get()`, `create()` and `getOrCreate()`:

* `get()` uses `\Phalcon\Mvc\Model::findFirst()` to get a model instance from the database.
* `create()` creates a model instance from dispatcher parameters but does not save it.
* `getOrCreate()` uses `get()` and then `create()` if it can't find a record in the database.

They all use the same parameters and work in the following way:

```php
namespace Sid\Controllers;

/**
 * @RoutePrefix("/post")
 */
class PostController extends \Phalcon\Mvc\Controller
{
    /**
     * @Route("/{categorySlug:[A-Za-z0-9\-]+}/{postSlug:[A-Za-z0-9\-]+}")
     */
    public function postAction()
    {
        // Infer attributes
        $post = $this->boundModels->get("Sid\\Models\\Posts");

        // Force only categorySlug to be used to find the model
        $post = $this->boundModels->get("Sid\\Models\\Posts", ["categorySlug"]);

        // ...
    }
}
```