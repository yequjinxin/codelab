<?php
namespace system;

class Db {
    static $db = null;
    public $conn = null;
    private function __construct() {
        $config = get_config()['db']['mysql'];
        $this->conn = mysqli_connect($config['host'], $config['user'], $config['pwd'], $config['db']);
    }

    static public function getInstance() {
        if (!(self::$db instanceof self)) {
            self::$db = new self();
        }
        return self::$db;
    }

    function find($sql) {
        $data = array();
        $query = mysqli_query($this->conn, $sql);
        while ($row = mysqli_fetch_assoc($query)) {
            $data[] = $row;
        }
        return $data;
    }

    function add($sql) {
        $res = mysqli_query($this->conn, $sql);
        if ($res) {
            $id = mysqli_insert_id($this->conn);
        } else {
            $id = false;
        }
        return $id;
    }
    
    function error() {
        return mysqli_error($this->conn);
    }

    function update($sql) {
        $res = mysqli_query($this->conn, $sql);
        return $res;
    }
}
