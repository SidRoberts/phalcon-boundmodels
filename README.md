Sid\Phalcon\BoundModels
=======================

Automatically get models based on dispatcher parameters within the Phalcon framework.



## Installing ##

Install using Composer:

```
{
	"require": {
		"sidroberts/phalcon-boundmodels": "dev-master"
	}
}
```



## Example ##

### DI ###

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