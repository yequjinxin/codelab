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

        if (!$this->isLogin) {
           $this->redirect('index.php?c=user&a=login');
        }
    }

    protected function getUserInfo() {
        $typeId = isset($this->user['id']) ? $this->user['id'] : 0;
        $user = $this->db->find("select * from user where type='weibo' and type_id='$typeId' and status not in (0,9)");
        return $user;
    }
}
