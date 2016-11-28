<?php
namespace app;

class User extends \system\Controller {
    function login() {
        $this->display('project', array('loginUrl' => $this->loginUrl));
    }

    /**
     * weibo回调
     */
    function weiboCallback() {
        $config = get_config('weibo');
        $o = new \app\lib\SaeTOAuthV2($config['WB_AKEY'], $config['WB_SKEY']);
        $token = '';
        if (isset($_REQUEST['code'])) {
            $keys = array();
            $keys['code'] = $_REQUEST['code'];
            $keys['redirect_uri'] = $config['WB_CALLBACK_URL'];
            $token = $o->getAccessToken('code', $keys) ;
        }
        if ($token) {
            $_SESSION['token'] = $token;
            setcookie('weibojs_' . $o->client_id, http_build_query($token));

            $logInfo = $this->checkLogin();
            $this->user = $logInfo['user'];
            // 存储用户信息
            if (!empty($this->user) && !isset($this->user['error_code']) && !isset($this->user['error'])) {
                $userId = $this->user['id'];
                $user = $this->db->find("select * from user where type='weibo' and type_id='$userId'");
                if (empty($user)) {
                    $name = $this->user['screen_name'];
                    $image = $this->user['profile_image_url'];
                    $url = $this->user['profile_url'];
                    $res = $this->db->add("insert into user (type,type_id,name,image,url,status)
                        values('weibo','$userId','$name','$image','$url',1)");
                    if (!$res) {
                        show_error('db error:' . $this->db->error(), '用户信息保存错误');
                    } else {
                        $now = date('Y-m-d H:i:s');
                        $sql = "insert into folder (uid,name,status,description,create_time) values ('$res','default',1,'','$now')";
                        $folderRes = $this->db->add($sql);
                        if (!$folderRes) {
                            show_error('db error:' . $this->db->error(), 'folder add error');
                        }
                    }
                }
                $this->redirect('index.php');
            } else {
                echo '授权失败:' . $this->user['error'] . ':' . $this->user['error_code'];
            }
        } else {
            echo '授权失败';
        }
    }
    /**
     * 登出
     */
    function logout() {
        unset($_SESSION['token']);
        unset($_SESSION['user']);
        $this->redirect('index.php');
    }
}
