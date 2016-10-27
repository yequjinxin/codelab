<?php
namespace system;

class PDO {
    static $db = null;
    public $conn = null;
    private function __construct() {
        $config = get_config()['db']['mysql'];
        $this->conn = new \PDO($config['dsn'], $config['user'], $config['pwd']);
    }

    static public function getInstance() {
        if (!(self::$db instanceof self)) {
            self::$db = new self();
        }
        return self::$db;
    }

    function find($sql) {
        $data = array();
        $query = $this->conn->query($sql);
        while ($row = $query->fetch()) {
            $data[] = $row;
        }
        return $data;
    }

    function add($sql) {
        $res = $this->conn->exec($sql);
        if ($res) {
            $id = $this->conn->lastInsertId();
        } else {
            $id = false;
        }
        return $id;
    }

    function error() {
        return $this->conn->errorInfo()[2];
    }

    function update($sql) {
        $res = $this->conn->exec($sql);
        return $res;
    }
}
