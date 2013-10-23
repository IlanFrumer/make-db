<?php 

namespace MakeDB\Lib;

class Benchmark implements \MakeDB\Interfaces\Wrapper
{
    private $_message;
    private $_title;
    private $_output;
    private $_ms;

    private static function format($diff)
    {
        $sec = intval($diff);
        $micro = $diff - $sec;
        return strftime('%T', mktime(0, 0, $sec)) . str_replace('0.', '.', sprintf('%.3f', $micro));
    }


    public function wrap(callable $callable)
    {
        $start = microtime(true);

        $params = $callable->__invoke();

        $end = microtime(true);

        $this->_title   = $params[0];
        $this->_message = $params[1];

        $diff = $end - $start;

        $this->_ms = self::format($diff);

        return $this;
    }

    public function __toString()
    {
        if (is_null($this->_title)) {
            return "";
        }
        $str = '';
        $str.= "<table><tr>";

        $str.= "<td><strong>---> ".$this->_ms."ms</strong></td>";
        $str.= "<td style='width:120px;text-align:right'><strong>".$this->_title."</strong> : </td>";
        $str.= "<td>".$this->_message."</td>";
        $str.= "</tr></table>".str_pad('', 4096);
        return $str;
    }
}
