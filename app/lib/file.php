<?php
namespace app\lib;

class File {
    static function deleteDir($path) {
        $op = dir($path);
        while (false !== ($item = $op->read())) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (is_dir($op->path . '/' . $item)) {
                self::deleteDir($op->path . '/' . $item);
                rmdir($op->path . '/' . $item);
            } else {
                unlink($op->path . '/' . $item);
            }
        }
    }

    static function writeData($dir, $data = null) {
        $pos = strrpos($dir, '/');
        $prefix = substr($dir, 0, $pos);
        if (!file_exists($prefix)) {
            self::writeData($prefix);
        }
        if ($data !== null) {
            file_put_contents($dir, $data);
        } else {
            mkdir($dir);
        }
    }

    static function writeFile($filePath, $content, $identify) {
        $dir = ROOT . 'app/sandbox/' . $identify . '/source';
        $filePath = array_reverse($filePath);
        foreach ($filePath as $v) {
            $dir .= '/' . $v;
        }
        // file_put_contents($dir, $content);
        self::writeData($dir, $content);
    }
}

?>