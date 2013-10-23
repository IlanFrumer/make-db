<?php 

namespace MakeDB\Interfaces;

interface Wrapper
{
    public function wrap(callable $callable);
    public function __toString();
}
