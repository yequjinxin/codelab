<?php
namespace system;

class Controller {
    protected $db = null;
    protected $user = '';
    protected $loginUrl = '';
    protected $loginType = '';
    protected $isLogin = false;

    function __construct() {
        session_start();
        ob_start();
        $config = get_config()['db']['mysql'];

        if ($config['driver'] === 'mysqli') {
            $this->db = \system\Db::getInstance();
        } else if ($config['driver'] === 'PDO') {
            $this->db = \system\PDO::getInstance();
        }
        $logInfo = $this->checkLogin();
        $this->user = $logInfo['user'];
        $this->loginUrl = $logInfo['loginUrl'];
        $this->loginType = $logInfo['loginType'];
    }

    // 目前只有微博
    function checkLogin() {
        $token = isset($_SESSION['token']) ? $_SESSION['token'] : '';
        $loginUrl['weibo'] = \app\lib\Login::getWeiboUrl();
        $loginType = 'weibo';
        if (empty($token)) {
            $user = '';
        } else {
            $user = \app\lib\Login::getWeiboUser();
        }
        return array('user' => $user, 'loginUrl' => $loginUrl, 'loginType' => $loginType);
    }

    protected function display($name, $data = array()) {
        $pos = strrpos($name, '.');
        if ($pos === 0) {
            show_error("模板文件{$name}不合法");
        }
        if ($pos === false) {
            $controller = $name;
        } else {
            $controller = substr($name, 0, $pos);
        }

        $arr = explode('.', $name);
        if (end($arr) !== 'html') {
            $name = $name . '.html';
        }
        extract($data);

        require ROOT . 'app/template/' . $name;

        $view = ob_get_contents();
        $view = str_replace(array("\n", "\r\n", "\r"), "", $view);
        preg_match('/{extends}(.*){extends}/i', $view, $extends);
        if (isset($extends[1]) && !empty($extends[1])) {

            preg_match('/{header}(.*){header}/i', $view, $header);
            preg_match('/{content}(.*){content}/i', $view, $content);
            preg_match('/{footer}(.*){footer}/i', $view, $footer);
            $header = isset($header[1]) ? $header[1] : '';
            $content = isset($content[1]) ? $content[1] : '';
            $footer = isset($footer[1]) ? $footer[1] : ''; 
            
            ob_clean();
            require ROOT . 'app/template/' . $extends[1];

            $baseView = ob_get_contents();
            $baseView = str_replace(
                array('{header}', '{content}', '{footer}'),
                array($header, $content, $footer),
                $baseView
            );
            ob_clean();
            echo $baseView;
        }
        ob_flush();
    }
    protected function redirect($url) {
        header("Location: $url");
        exit;
    }
}
