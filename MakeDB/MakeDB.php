<?php 

namespace MakeDB;

class MakeDB
{
    private $_tasks = [];
    private $_services = [];
    
    public $db;

    public function __call($method, $args)
    {
        if ($method == 'service') {

            switch (count($args)) {
                case 2:
                    return $this->_service($args[0], [], $args[1]);
                case 3:
                    return $this->_service($args[0], $args[1], $args[2]);
                default:
                    self::_argumnetsError($method, 2, 3);
                    return;
            }

        } else {
            throw new \Exception("Method ($method) does not exists", 1);
        }
    }

    public function mapService($service, array $map, array $dependencies, callable $provider)
    {
        if (array_key_exists($service, $this->_services)) {
            throw new \Exception("services name colision($service)");
        }
        $this->_services[$service] = new MapService($this, $map, $dependencies, $provider);
    }

    private function _service($service, array $dependencies, $provider)
    {
        if (array_key_exists($service, $this->_services)) {
            throw new \Exception("services name colision($service)");
        }

        $wrappedProvider = self::wrapProvider($provider);
        $this->_services[$service] = new Service($this, $dependencies, $wrappedProvider);
    }

    public function using(Interfaces\Database $database)
    {
        $this->db = $database;
        return $this;
    }


    public function table($table)
    {
        return new Table($this, $table);
    }

    public function getServices(array $services)
    {
        $params = [];
        foreach ($services as $service) {
            $params[] = $this->getService($service);
        }
        return $params;
    }

    public function getService($service)
    {
        if (array_key_exists($service, $this->_services)) {
            return $this->_services[$service]->get();
        } else {
            throw new \Exception("cannot find service ($service)");
        }
    }

    public function register(array $dependencies, callable $callback, $title = null, $message = null)
    {
        $task = new Task($this, $dependencies, $callback, $title, $message);
        $this->_tasks[] = $task;
        return $task;
    }

    public function get($dependencies, callable $callback)
    {
        if (is_string($dependencies)) {
            $dependencies = [$dependencies];
        }
        if (!is_array($dependencies)) {
            throw new \Exception("get method first argument must be a string or array", 1);
        }
        $this->register($dependencies, $callback);
    }


    private static function runTask($task, $wrapper = null)
    {
        if (is_null($wrapper)) {
            $task->run();
        } else {
            $w = new $wrapper();
            
            $w->wrap(function () use ($task) {
                $task->run();
                return [$task->title , $task->message];
            });

            echo $w;
        }
        return $task->provider;
    }

    public function run(Interfaces\Wrapper $wrapper = null)
    {
        
        foreach ($this->_tasks as $task) {
            self::runTask($task, $wrapper);
        }

        $tasks = [];
        
    }

    private static function _argumnetsError($method, $argmin, $argmax = null)
    {
        if (is_null($argmax)) {
            throw new \Exception("$method only excepts $argmin arguments", 1);
        } else {
            throw new \Exception("$method method only excepts beeween $argmin or $argmax arguments", 1);
        }
    }

    private static function wrapProvider($provider)
    {
        if (is_object($provider)) {


            $class = get_class($provider);

            if ($class == "Closure") {
                return $provider;
            } else if ($class == "MakeDB\Task") {
                return function () use ($provider) {
                    return self::runTask($provider);
                };
            }
            exit();
        }
        return function () use ($provider) {
            return $provider;
        };
    }
}
