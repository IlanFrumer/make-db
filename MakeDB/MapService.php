<?php 

namespace MakeDB;

class MapService extends Service
{

    public function __construct(MakeDB $MakeDB, array $map, array $dependecies, callable $user_provider)
    {
        $this->MakeDB       = $MakeDB;
        $this->dependecies  = $dependecies;
        $this->provider = function () use ($map, $user_provider) {
            
            $args = func_get_args();
            $mapped = [];
            foreach ($map as $idx => $element) {
                $arguments = array_merge([$idx, $element], $args);
                $mapped[] = call_user_func_array($user_provider, $arguments);
            }
            return $mapped;
        };
    }
}
