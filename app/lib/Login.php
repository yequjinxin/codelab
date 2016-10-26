<?php
namespace app\lib;

class Login {
    static function getWeiboUrl() {
        $config = get_config('weibo');
        $o = new \app\lib\SaeTOAuthV2($config['WB_AKEY'], $config['WB_SKEY']);
        $url = $o->getAuthorizeURL($config['WB_CALLBACK_URL']);
        return $url;
    }

    /**
     * 查询微博用户信息
     */
    static function getWeiboUser() {
        $config = get_config('weibo');
        $c = new \app\lib\SaeTClientV2($config['WB_AKEY'], $config['WB_SKEY'], $_SESSION['token']['access_token']);
        $ms  = $c->home_timeline(); // done
        $uid_get = $c->get_uid();
        $uid = $uid_get['uid'];
        $userMessage = $c->show_user_by_id($uid); // 根据ID获取用户等基本信息
        return $userMessage;
    }
}
