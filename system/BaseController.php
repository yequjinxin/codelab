<?php
namespace system;

class BaseController extends Controller {
    private $projectInfo = array();

    function __construct() {
        parent::__construct();
        if ($this->loginType === 'weibo'
            && (empty($this->user) || (isset($this->user['error']) && isset($this->user['error_code'])))) {
            $this->isLogin = false;
        } else {
            $this->isLogin = true;
        }

        $this->isOpen = false; // 公开访问,不包含指定用户登录访问公开项目
        $user = $this->getUserInfo();
        if ($this->checkProject()) {
            $this->isOpen = true;
            // 公开项目有一种情况不能使用公开模式：公开项目所有者访问公开项目
            if ($this->isLogin) {
                if (!empty($this->projectInfo[0]) && ($this->projectInfo[0]['user'] == $user[0]['id'])) {
                    $this->isOpen = false;
                }
            }
        } else {
            if ($this->isLogin) {
                // 登录访问非自己所有的私有项目
                if (!empty($this->projectInfo[0]) && ($this->projectInfo[0]['user'] != $user[0]['id'])) {
                    $this->redirect('index.php?c=user&a=login&deny=1');
                }
            } else {
                $this->redirect('index.php?c=user&a=login');
            }
        }
    }

    protected function getUserInfo() {
        static $user;
        if ($user) {
            return $user;
        }
        $typeId = isset($this->user['id']) ? $this->user['id'] : 0;
        $user = $this->db->find("select * from user where type='weibo' and type_id='$typeId' and status not in (0,9)");
        return $user;
    }

    protected function checkProject() {
        // 指定路由下的公开项目
        if ((empty($_GET['c']) || strtolower($_GET['c']) === 'index')
            && (strtolower($_GET['a']) === 'main' || strtolower($_GET['a']) === 'getcodes' || strtolower($_GET['a']) === 'runcode')) {
            // 判断项目是否公开
            $id = empty($_GET['pro_id']) ? 0 : $_GET['pro_id'];
            if (empty($id)) {
                $id = isset($_POST['proId']) ? $_POST['proId'] : 0;
            }
            $ret = $this->db->find("select * from project where id=$id");
            $this->projectInfo = $ret;
            if (!empty($ret) && $ret[0]['is_open']) {
                return true;
            } else {
                return false;
            }
        }
    }
}
