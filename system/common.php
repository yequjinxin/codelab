<?php
function get_config($name = 'config') {
    static $configs = array();
    if (empty($configs[$name])) {
        $file_path = ROOT . 'app/config/' . $name . '.php';
        if (file_exists($file_path)) {
            require($file_path);
            $configs[$name] = $config;
        }
    }
    return $configs[$name];
}

function show_error($msg, $secure_msg = '') {
    $debug = get_config()['debug'];
    if ($debug) {
        echo $msg;
    } else {
        if (empty($secure_msg)) {
            echo $msg;
        } else {
            echo $secure_msg;
        }
    }
    exit();
}

function show_msg($msg) {
    echo $msg;
    exit();
}

/**
 * 转义数组
 * @param string $string
 * 
 * @return string $string
 */
function daddslashes($string) {
    if (is_array($string)) {
        foreach ($string as $key => $value) {
            $string[$key] = daddslashes($value);
        }
    } else {
        characterDetect($string);
        $string = addslashes($string);
    }
    return $string;
}

/**
 * 检测非法字符
 * @param $string
 */
function characterDetect($string) {
    if (preg_match('/\bunion\b.*?\bselect\b/i', $string)
            || preg_match('/and UpdateXML\(/i', $string)) {
        $url = $_SERVER['REQUEST_URI'];
        $ip = $_SERVER['REMOTE_ADDR'];
        file_put_contents('./log.txt',
        date('Y-m-d H:i:s') . '---' . $url . '---(' . $ip . ')---' . $string . "\n", FILE_APPEND);
        show_msg('检测到URL非法字符, 此事件已被记录...');
    }
}
