<?php

namespace Sid\Phalcon\BoundModels;

use Exception;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Mvc\User\Plugin;

class Manager extends Plugin
{
    /**
     * @var int
     */
    protected $paramSource = self::DISPATCHER;

    protected $customParams = null;



    const DISPATCHER   = 1;
    const REQUEST_GET  = 2;
    const REQUEST_POST = 3;



    public function setParamSource(int $paramSource)
    {
        $this->paramSource = $paramSource;
    }

    public function setCustomParamSource(array $customParams)
    {
        $this->customParams = $customParams;
    }



    /**
     * @return ModelInterface|false
     */
    public function get(string $className, array $acceptableAttributes = null)
    {
        if (!$acceptableAttributes) {
            $acceptableAttributes = $this->getDefaultAcceptableAttributes(
                $className
            );
        }

        $parameters = $this->buildModelParameters($acceptableAttributes);

        $boundModel = call_user_func_array(
            [$className, "findFirst"],
            [
                $parameters,
            ]
        );

        return $boundModel;
    }

    public function create(string $className, array $acceptableAttributes = null) : ModelInterface
    {
        if (!$acceptableAttributes) {
            $acceptableAttributes = $this->getDefaultAcceptableAttributes(
                $className
            );
        }

        $data = [];

        foreach ($acceptableAttributes as $attribute) {
            $data[$attribute] = $this->getParam($attribute);
        }

        $boundModel = new $className();

        $boundModel->assign($data);

        return $boundModel;
    }

    public function getOrCreate(string $className, array $acceptableAttributes = null) : ModelInterface
    {
        $boundModel = $this->get($className, $acceptableAttributes);

        if (!$boundModel) {
            $boundModel = $this->create($className, $acceptableAttributes);
        }

        return $boundModel;
    }

    public function exists(string $className, array $acceptableAttributes = null) : bool
    {
        if (!$acceptableAttributes) {
            $acceptableAttributes = $this->getDefaultAcceptableAttributes(
                $className
            );
        }

        $parameters = $this->buildModelParameters($acceptableAttributes);

        $parameters["limit"] = 1;

        $count = call_user_func_array(
            [$className, "count"],
            [
                $parameters,
            ]
        );

        return ($count > 0);
    }



    protected function getDefaultAcceptableAttributes(string $className) : array
    {
        $model = new $className();

        $dispatcherParams = array_keys($this->getParams());
        $modelAttributes  = $this->modelsMetadata->getAttributes($model);

        $acceptableAttributes = array_intersect(
            $dispatcherParams,
            $modelAttributes
        );

        return $acceptableAttributes;
    }



    protected function getParam($name)
    {
        if (is_array($this->customParams)) {
            return $this->customParams[$name];
        }

        switch ($this->paramSource) {
            case self::DISPATCHER:
                return $this->dispatcher->getParam($name);

            case self::REQUEST_GET:
                return $this->request->getQuery($name);

            case self::REQUEST_POST:
                return $this->request->getPost($name);

            default:
                throw new Exception(
                    "Param source not found."
                );
        }
    }

    protected function getParams()
    {
        if (is_array($this->customParams)) {
            return $this->customParams;
        }

        switch ($this->paramSource) {
            case self::DISPATCHER:
                return $this->dispatcher->getParams();

            case self::REQUEST_GET:
                return $this->request->getQuery();

            case self::REQUEST_POST:
                return $this->request->getPost();

            default:
                throw new Exception(
                    "Param source not found."
                );
        }
    }



    protected function buildModelParameters(array $attributes) : array
    {
        $conditions = [];
        $bind       = [];

        foreach ($attributes as $attribute) {
            $conditions[]     = $attribute . " = :" . $attribute . ":";
            $bind[$attribute] = $this->getParam($attribute);
        }

        $conditions = implode(" AND ", $conditions);

        return [
            "conditions" => $conditions,
            "bind"       => $bind,
        ];
    }
}
