<?php 

namespace MakeDB;

class Service
{
    protected $MakeDB;
    protected $dependecies;
    protected $provider;

    protected $instance = null;
    protected $invoked  = false;

    public function __construct(MakeDB $MakeDB, array $dependecies, callable $provider)
    {
        $this->MakeDB       = $MakeDB;
        $this->dependecies  = $dependecies;
        $this->provider     = $provider;
    }

    public function get()
    {
        
        if (is_null($this->instance)) {
            if ($this->invoked) {
                throw new \Exception("recursive dependencies ($this->service)");
            }
        
            $this->invoked = true;
            $params = $this->MakeDB->getServices($this->dependecies);
            $this->instance = call_user_func_array($this->provider, $params);
        }
        
        return $this->instance;
    }
}
