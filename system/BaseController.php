<?php
namespace system;

class BaseController extends Controller {

    function __construct() {
        parent::__construct();
        if ($this->loginType === 'weibo'
            && (empty($this->user) || (isset($this->user['error']) && isset($this->user['error_code'])))) {
            $this->isLogin = false;
        } else {
            $this->isLogin = true;
        }
        $this->isOpen = false;
        if (!$this->isLogin) {
            if ($this->checkProject()) {
                $this->isOpen = true;
            } else {
                $this->redirect('index.php?c=user&a=login');
            }
        }
    }

    protected function getUserInfo() {
        $typeId = isset($this->user['id']) ? $this->user['id'] : 0;
        $user = $this->db->find("select * from user where type='weibo' and type_id='$typeId' and status not in (0,9)");
        return $user;
    }

    protected function checkProject() {
        if ((empty($_GET['c']) || strtolower($_GET['c']) === 'index')
            && (strtolower($_GET['a']) === 'main' || strtolower($_GET['a']) === 'getcodes')) {
            // 判断项目是否公开
            $id = empty($_GET['pro_id']) ? 0 : $_GET['pro_id'];
            $id = empty($id) ? 0 : $_POST['proId'];
            if (empty($id)) {
                $id = isset($_POST['proId']) ? $_POST['proId'] : 0;
            }
            $ret = $this->db->find("select * from project where id=$id");
            if (!empty($ret) && $ret[0]['is_open']) {
                return true;
            } else {
                return false;
            }
        }
    }
}
