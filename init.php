<?php
/**
 * 自动加载 psr-4
 * https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
 */
include SYSTEM . 'autoload.php';
$autoload = new \system\Psr4AutoloaderClass();
$autoload->addNamespace(
    'app',
    ROOT . 'app/controller'
);
$autoload->addNamespace(
    'system',
    SYSTEM
);
$autoload->addNamespace(
    'app\lib',
    ROOT . 'app/lib'
);
$autoload->register();

// 全局函数
require SYSTEM . 'common.php';

// 转义处理
$_GET = daddslashes($_GET);
foreach ($_POST as $k => $v) {
    $arrTmp = json_decode($v, true);
    if ($arrTmp === null) {
        $_POST[$k] = addslashes($v);
    } else if (is_array($arrTmp)) {
        $arrTmp = daddslashes($arrTmp);
        $_POST[$k] = json_encode($arrTmp);
    }
}
