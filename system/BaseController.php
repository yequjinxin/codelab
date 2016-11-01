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
}
