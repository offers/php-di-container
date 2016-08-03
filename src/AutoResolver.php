<?php
namespace Offers\Di;

class AutoResolver
{
    protected $argumentResolverCache = [];

    /** @var Container */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $className
     * @return object
     */
    public function new($className)
    {
        $params = $this->resolveArguments($className, "__construct");
        $rc = new \ReflectionClass($className);
        if ($params) {
            $callParams = [];
            foreach ($params as $param) {
                $paramType = $param->getType();
                $paramName = $param->getName();
                if ($paramType) {
                    if ($this->container->has($paramType, $paramName)) {
                        $callParams[] = $this->container->get($paramType, $paramName);
                    } else if ($this->container->has($paramType)) {
                        $callParams[] = $this->container->get($paramType);
                    } else {
                        throw new \RuntimeException("could not resolve argument $paramName ($paramName)");
                    }
                } else {
                    throw new \RuntimeException("could not resolve non-object argument $paramName");
                }
            }
            return $rc->newInstanceArgs($callParams);
        } else {
            throw new \RuntimeException("no constructor params found ($className), nothing to inject");
        }
    }

    public function execute($object, $method)
    {
        $className = get_class($object);
        $params = $this->resolveArguments($className, $method);
        $callParams = [];
        if ($params) {
            foreach ($params as $param) {
                $paramType = $param->getType();
                $paramName = $param->getName();
                if ($paramType) {
                    if ($this->container->has($paramType, $paramName)) {
                        $callParams[] = $this->container->get($paramType, $paramName);
                    } else if ($this->container->has($paramType)) {
                        $callParams[] = $this->container->get($paramType);
                    } else {
                        throw new \RuntimeException("could not resolve argument $paramName ($paramName)");
                    }
                } else {
                    throw new \RuntimeException("could not resolve non-object argument $paramName");
                }
            }
        }
        return call_user_func_array([$object, $method], $callParams);
    }

    protected function resolveArguments($className, $methodName)
    {
        if (isset($this->argumentResolverCache["$className.$methodName"])) {
            return $this->argumentResolverCache["$className.$methodName"];
        } else {
            $methodParams = [];
            if (method_exists($className, $methodName)) {
                $r = new \ReflectionMethod($className, $methodName);
                $methodParams = $r->getParameters();
            }
            $this->argumentResolverCache["$className.$methodName"] = $methodParams;
            return $methodParams;
        }
    }
}