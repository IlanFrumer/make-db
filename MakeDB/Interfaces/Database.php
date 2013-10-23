<?php

namespace MakeDB\Interfaces;

interface Database
{
    public function exec($query);
    public function getAll($query, $params = null);
    public function getOne($query, $params = null);
    public function insert($query, $params);
    public function getLast();
    public function rowCount();
}
