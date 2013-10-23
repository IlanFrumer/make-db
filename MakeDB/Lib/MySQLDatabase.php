<?php

namespace MakeDB\Lib;

use \PDO;
use \PDOException;

class MySQLDatabase implements \MakeDB\Interfaces\Database
{

    private $_dbh;
    private $_stmt;
 
    public function __construct($host, $dbname, $user, $pass)
    {

        // Set DSN
        $dsn = 'mysql:host=' . $host . ';dbname=' . $dbname;
        // Set options
        $options = [
            PDO::ATTR_PERSISTENT         => true,
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        ];

        try {
            $this->_dbh = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            $this->_error($e);
        }
    }

    private function _error(PDOException $e, $query = null, $params = null)
    {

        $error = [];

        $error['Query'] = $query;
        $error['Params'] = $params;
        $error['Message'] = $e->getMessage();
        // $error['Previous'] = $e->getPrevious();
        // $error['Code']     =$e->errorInfo[0];
        // $error['SQL_Code'] = $e->errorInfo[1];
        // $error['File'] = $e->getFile();
        // $error['Line'] = $e->getLine();
        
        echo "<pre>";
        print_r($error);
        echo "</pre>";
        die();
    }


    private function bind($params)
    {
        try {


            foreach ($params as $idx => $value) {
                
                $type = is_null($value) ? PDO::PARAM_NULL : PDO::PARAM_STR;
                $type = is_bool($value) ? PDO::PARAM_BOOL : PDO::PARAM_STR;
                $type = is_integer($value) ? PDO::PARAM_INT : PDO::PARAM_STR;

                $this->_stmt->bindValue($idx+1, $value, $type);
            }
        } catch (PDOException $e) {
            $this->_error($e, null, $params);
        }
    }


    private function _query($query, $params = null)
    {
        try {

            $this->_stmt = $this->_dbh->prepare($query);
            $this->bind($params);
            return $this->_stmt->execute();
        } catch (PDOException $e) {
            $this->_error($e, $query, $params);
        }
    }

    public function exec($query)
    {
        try {
            return $this->_dbh->exec($query);
        } catch (PDOException $e) {
            $this->_error($e, $query);
        }
    }

    public function getAll($query, $params = null)
    {
        $this->_query($query, $params);
        return $this->_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOne($query, $params = null)
    {
        $this->_query($query, $params);
        return $this->_stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($query, $params)
    {
        $this->_query($query, $params);
        return $this->_dbh->lastInsertId();
    }

    public function getLast()
    {
        return $this->_dbh->lastInsertId();
    }

    public function rowCount()
    {
        return $this->_stmt->rowCount();
    }
}
