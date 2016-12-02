<?php
namespace system;

class Db {
    static private $db = null;
    public $connRead = null;
    public $connWrite = null;
    private function __construct() {
        $configRead = get_config()['db']['mysql']['read'];
        $configWrite = get_config()['db']['mysql']['write'];
        $this->connRead = mysqli_connect($configRead['host'], $configRead['user'], $configRead['pwd'], $configRead['db']);
        $this->connWrite = mysqli_connect($configWrite['host'], $configWrite['user'], $configWrite['pwd'], $configWrite['db']);
    }

    static public function getInstance() {
        if (!(self::$db instanceof self)) {
            self::$db = new self();
        }
        return self::$db;
    }

    private function  __clone() {
    }

    function find($sql) {
        $data = array();
        $query = mysqli_query($this->connRead, $sql);
        while ($row = mysqli_fetch_assoc($query)) {
            $data[] = $row;
        }
        return $data;
    }

    function add($sql) {
        $res = mysqli_query($this->connWrite, $sql);
        if ($res) {
            $id = mysqli_insert_id($this->connWrite);
        } else {
            $id = false;
        }
        return $id;
    }
    
    function error() {
        return mysqli_error($this->connWrite);
    }

    function update($sql) {
        $res = mysqli_query($this->connWrite, $sql);
        return $res;
    }
}
