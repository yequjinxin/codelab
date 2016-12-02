<?php
namespace system;

class PDO {
    static $db = null;
    public $connRead = null;
    public $connWrite = null;
    private function __construct() {
        $configRead = get_config()['db']['mysql']['read'];
        $configWrite = get_config()['db']['mysql']['write'];
        $this->connRead = new \PDO($configRead['dsn'], $configRead['user'], $configRead['pwd']);
        $this->connWrite = new \PDO($configWrite['dsn'], $configWrite['user'], $configWrite['pwd']);
    }

    static public function getInstance() {
        if (!(self::$db instanceof self)) {
            self::$db = new self();
        }
        return self::$db;
    }

    function find($sql) {
        $data = array();
        $query = $this->connRead->query($sql);
        while ($row = $query->fetch()) {
            $data[] = $row;
        }
        return $data;
    }

    function add($sql) {
        $res = $this->connWrite->exec($sql);
        if ($res) {
            $id = $this->connWrite->lastInsertId();
        } else {
            $id = false;
        }
        return $id;
    }

    function error() {
        return $this->connWrite->errorInfo()[2];
    }

    function update($sql) {
        $res = $this->connWrite->exec($sql);
        return $res;
    }
}
