<?php

namespace Sid\Phalcon\BoundModels;

class Manager extends \Phalcon\Mvc\User\Plugin
{
    protected $paramSource = self::DISPATCHER;
    protected $customParams = null;

    const DISPATCHER   = 1;
    const REQUEST_GET  = 2;
    const REQUEST_POST = 3;



    /**
     * @param integer $paramSource
     */
    public function setParamSource($paramSource)
    {
        $this->paramSource = $paramSource;
    }

    /**
     * @param array $customParams
     */
    public function setCustomParamSource(array $customParams)
    {
        $this->customParams = $customParams;
    }



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
            $bind[$attribute] = $this->getParam($attribute);
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
     * @param string     $className
     * @param array|null $acceptableAttributes
     *
     * @return \Phalcon\Mvc\ModelInterface
     */
    public function create($className, $acceptableAttributes = null)
    {
        if (!$acceptableAttributes) {
            $acceptableAttributes = $this->getDefaultAcceptableAttributes($className);
        }

        $data = [];

        foreach ($acceptableAttributes as $attribute) {
            $data[$attribute] = $this->getParam($attribute);
        }

        $boundModel = new $className();

        $boundModel->assign($data);

        return $boundModel;
    }

    /**
     * @param string     $className
     * @param array|null $acceptableAttributes
     *
     * @return \Phalcon\Mvc\ModelInterface
     */
    public function getOrCreate($className, $acceptableAttributes = null)
    {
        $boundModel = $this->get($className, $acceptableAttributes);

        if (!$boundModel) {
            $boundModel = $this->create($className, $acceptableAttributes);
        }

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

        $dispatcherParams = array_keys($this->getParams());
        $modelAttributes  = $this->modelsMetadata->getAttributes($model);

        $acceptableAttributes = array_intersect($dispatcherParams, $modelAttributes);

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
                throw new \Exception("Param source not found.");
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
                throw new \Exception("Param source not found.");
        }
    }
}
