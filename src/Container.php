<?php
namespace Offers\Di;

class Container
{
    protected $factories = [];
    protected $shared = [];

    /** @var  AutoResolver */
    protected $autoResolver;

    /**
     * @param string $className
     * @param callable $factory
     * @param string|null $instanceName
     */
    public function service(string $className, callable $factory, string $instanceName = null)
    {
        $factoryKey = $this->getFactoryKey($className, $instanceName);
        $this->factories[$factoryKey] = [
            "factory" => $factory,
            "shared" => true
        ];
    }

    /**
     * @param string $className
     * @param callable $factory
     * @param string|null $instanceName
     */
    public function factory(string $className, callable $factory, string $instanceName = null)
    {
        $factoryKey = $this->getFactoryKey($className, $instanceName);
        $this->factories[$factoryKey] = [
            "factory" => $factory,
            "shared" => false
        ];
    }

    /**
     * @param string $className
     * @param string|null $instanceName
     * @return mixed
     */
    public function get(string $className, string $instanceName = null)
    {
        $factoryKey = $this->getFactoryKey($className, $instanceName);
        if (isset($this->factories[$factoryKey])) {
            list($factory, $shared) = array_values($this->factories[$factoryKey]);
            if ($shared) {
                if (!isset($this->shared[$factoryKey])) {
                    $this->shared[$factoryKey] = call_user_func_array($factory, [$this]);
                }
                return $this->shared[$factoryKey];
            } else {
                return call_user_func_array($factory, [$this]);
            }
        } else {
            throw new \RuntimeException("failed to find factory for $factoryKey");
        }
    }

    public function set($className, $instance, $instanceName = null, $shared = true)
    {
        $factoryKey = $this->getFactoryKey($className, $instanceName);
        $this->factories[$factoryKey] = [
            "factory" => function() use ($instance) {
                return $instance;
            },
            "shared" => $shared
        ];
    }

    /**
     * @param string $className
     * @param string|null $instanceName
     * @return bool
     */
    public function has(string $className, string $instanceName = null)
    {
        $factoryKey = $this->getFactoryKey($className, $instanceName);
        return isset($this->factories[$factoryKey]);
    }

    public function getFactoryKey(string $className, string $instanceName = null)
    {
        $key = $className;
        if ($instanceName) {
            $key .= "_$instanceName";
        }
        return $key;
    }

    /**
     * @return AutoResolver
     */
    public function getAutoResolver()
    {
        if (!$this->autoResolver) {
            $this->autoResolver = new AutoResolver($this);
        }
        return $this->autoResolver;
    }


}