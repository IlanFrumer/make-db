<?php 

namespace MakeDB;

class Task
{
    public $title;
    public $message;
    public $provider;

    private $_callback;
    private $_dependencies = [];
    private $_dispatched = false;
    private $_MakeDB;

    public function __construct(MakeDB $MakeDB, array $dependencies, callable $callback, $title, $message)
    {

        $this->_MakeDB       = $MakeDB;
        $this->_dependencies = $dependencies;
        $this->_callback     = $callback;

        $this->title        = $title;
        $this->message      = $message;
    }


    private static function patternMatch($string, $params)
    {
        $pattern = '/\[\?\]/';

        // *bold*
        $string = preg_replace('/\*([^\*]+)\*/', '<strong>\1</strong>', $string);

        // [?]
        $string = preg_replace_callback($pattern, function () use (&$params) {
            return array_shift($params);
        }, $string);

        return $string;

    }

    public function run()
    {
        if ($this->_dispatched) {
            return;
        }
        $this->_dispatched = true;
        $params = $this->_MakeDB->getServices($this->_dependencies);
        
        $output = call_user_func_array($this->_callback, $params);
        $this->message  = self::patternMatch($this->message, $output[1]);
        $this->provider = $output[0];
    }
}
