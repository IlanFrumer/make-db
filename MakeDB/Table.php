<?php 

namespace MakeDB;

class Table
{
    private $_MakeDB;
    private $_db;
    private $_table;

    public function __construct(MakeDB $MakeDB, $table)
    {

        $this->_MakeDB = $MakeDB;
        $this->_db     = $MakeDB->db;
        $this->_table  = $table;
    }

    public function reset($option)
    {

        switch ($option) {
            case 'AUTO_INCREMENT':
                $default = 1;
                break;
            default:
                throw new \Exception("$option has no default value", 1);
        }
        return $this->set($option, $default);
    }

    public function set($option, $value)
    {
            
        $func = function () use ($option, $value) {
            $table = $this->_table;
            $result = $this->_db->exec("ALTER TABLE $table $option = $value");
            return [null, [$result]];
        };

        $message = "set *$option* to *$value* = [?]";
        $this->_MakeDB->register([], $func, $this->_table, $message);
        return $this;
    }


    public function delete()
    {
        
        $func = function () {
            $table = $this->_table;
            $result = $this->_db->exec("DELETE FROM $table");
            return [null, [$result]];
        };

        $message = "deleted *[?]* rows";
        $this->_MakeDB->register([], $func, $this->_table, $message);
        return $this;
    }

    public function insert($dependency, $map_output = null)
    {
        
        $func = function () use ($dependency, $map_output) {
            $table = $this->_table;

            $map = func_get_args()[0];

            foreach ($map as &$element) {
                $fileds_with_comma = join(' , ', array_keys($element));
                $fields = "( $fileds_with_comma )";
                $qmarks = preg_replace('/[^(),]+/', '?', $fields, -1);
                $prepare_sql = "INSERT INTO $table $fields VALUES $qmarks";
                $id = $this->_db->insert($prepare_sql, array_values($element));
                $element['$$id'] = $id;
            }
            
            return [$map, [$dependency , count($map)]];
        };

        $message = "inserted rows from *[?]* : [?]";
        
        if (!is_null($map_output)) {
            $message.=" --> *$map_output*";
        }
        
        $task = $this->_MakeDB->register([$dependency], $func, $this->_table, $message);

        if (!is_null($map_output)) {
            $this->_MakeDB->service($map_output, $task);
        }

        return $this;
    }

    public function insertR(array $dependencies, callable $callback)
    {
        
        $func = function () use ($callback) {

            $table = $this->_table;
            $args = func_get_args();
            $count = 1;

            do {
                $element = call_user_func_array($callback, array_merge([$count], $args));
                if ($element) {
                    $fileds_with_comma = join(' , ', array_keys($element));
                    $fields = '( $fileds_with_comma )';
                    $qmarks = preg_replace('/[^(),]+/', '?', $fields, -1);
                    $prepare_sql = "INSERT INTO $table $fields VALUES $qmarks";
                    $this->_db->insert($prepare_sql, array_values($element));
                    $count++;
                }

            } while ($element);

            return [null, [$count-1]];
        };

        $message = "recursive insert of [?] rows";
        
        $task = $this->_MakeDB->register($dependencies, $func, $this->_table, $message);

        return $this;
    }

    public function insertUpdateR(array $dependencies, callable $callback)
    {
        
        $func = function () use ($callback) {

            $table = $this->_table;
            $args = func_get_args();
            $count = 1;

            do {
                $element = call_user_func_array($callback, array_merge([$count], $args));
                if ($element) {

                    $fileds_with_comma = join(' , ', array_keys($element));
                    $fields = "( $fileds_with_comma )";
                    $qmarks = preg_replace('/[^(),]+/', '?', $fields, -1);

                    $set_fields = array_map(function ($key) {
                        return "$key = ?";
                    }, array_keys($element));

                    $set_fields = implode(" , ", $set_fields);

                    $prepare_sql = "INSERT INTO $table $fields VALUES $qmarks ON DUPLICATE KEY UPDATE $set_fields";

                    $values = array_values($element);
                    $this->_db->insert($prepare_sql, array_merge($values, $values));

                    $count++;
                }

            } while ($element);

            return [null, [$count-1]];
        };

        $message = "recursive insert\update of [?] rows";
        
        $task = $this->_MakeDB->register($dependencies, $func, $this->_table, $message);

        return $this;
    }

    public function updateR($index_field, array $dependencies, callable $callback)
    {
        
        $func = function () use ($callback, $index_field) {

            $table = $this->_table;
            $args = func_get_args();
            $count = 1;

            do {

                $element = call_user_func_array($callback, array_merge([$count], $args));

                if ($element) {

                    if (array_key_exists($index_field, $element)) {
                        $value = $element[$index_field];
                        unset($element[$index_field]);
                    }

                    $fields = array_map(function ($key) {
                        return "$key = ?";
                    }, array_keys($element));

                    $fields = implode(" , ", $fields);

                    $prepare_sql = "UPDATE $table SET $fields WHERE $index_field = $value";

                    $this->_db->insert($prepare_sql, array_values($element));
                    $count++;
                }

            } while ($element);

            return [null, [$count-1]];
        };

        $message = "recursive update of [?] rows";
        
        $task = $this->_MakeDB->register($dependencies, $func, $this->_table, $message);

        return $this;
    }
}
