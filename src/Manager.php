<?php

namespace Sid\Phalcon\BoundModels;

class Manager extends \Phalcon\Mvc\User\Plugin
{
    /**
     * @param string     $className
     * @param array|null $acceptableAttributes
     *
     * @return \Phalcon\Mvc\ModelInterface|false
     */
    public function get($className, $acceptableAttributes = null)
    {
        if (!$acceptableAttributes) {
            $acceptableAttributes = $this->getDefaultAcceptableAttributes($className);
        }

        $conditions = [];
        $bind       = [];

        foreach ($acceptableAttributes as $attribute) {
            $conditions[]     = $attribute . " = :" . $attribute . ":";
            $bind[$attribute] = $this->dispatcher->getParam($attribute);
        }

        $conditions = implode(" AND ", $conditions);

        $boundModel = call_user_func_array(
            [$className, "findFirst"],
            [
                [
                    "conditions" => $conditions,
                    "bind"       => $bind
                ]
            ]
        );

        return $boundModel;
    }

    /**
     * @param string $className
     *
     * @return array
     */
    protected function getDefaultAcceptableAttributes($className)
    {
        $model = new $className();

        $dispatcherParams = array_keys($this->dispatcher->getParams());
        $modelAttributes  = $this->modelsMetadata->getAttributes($model);

        $acceptableAttributes = array_intersect($dispatcherParams, $modelAttributes);

        return $acceptableAttributes;
    }
}
