/**
 * config
 */
define([], function () {
    // 保存代码二维数组
    var codes = {};
    // 文件和id的映射关系 例如：index.php:1
    var idMap = {};
    // 文件名和type的映射关系例如：index.php:php
    var typeMap = {};
    var nameMap = {};
    // 文件名是否在编辑中的标志位
    var isEditFileName = false;

    /**
     * mode是mirrorcode代码显示模式
     * fileName是默认的文件名
     * ext是文件后缀
     */
    var editorMode = {
        'html': {mode: 'text/html', fileName: 'index.html', /*ext: 'html'*/},
        'js': {mode: 'javascript', fileName: 'main.js', /*ext: 'js'*/},
        'php': {mode: 'application/x-httpd-php', fileName: 'index.php', /*ext: 'php'*/},
        'c': {mode: 'text/x-csrc', fileName: 'main.c', /*ext : 'c'*/},
        'py': {mode: 'text/x-python', fileName: 'main.py'},
    };

    // 设置文件信息
    function setComment(fileName, fileType) {
        var date = new Date();
        var str = '';

        switch (fileType) {
            case 'html':
                str = '<!DOCTYPE html>'
                    + '\n<html lang="en">'
                    + '\n<head>'
                    +     '\n    <meta charset="utf-8">'
                    +     '\n    <title></title>'
                    + '\n</head>'
                    + '\n<body>'

                    + '\n</body>'
                    + '\n</html>';
                break;
            case 'js':
            case 'php':
                var prefix = fileType === 'js' ? '' : '<?php\n';
                str = prefix
                    + '/**'
                    + '\n * @file ' + fileName
                    + '\n * @author '
                    + '\n * @datetime ' + date.format('yyyy-MM-dd hh:mm:ss')
                    + '\n **/'
                    + '\n';
                break;
            case 'c':
                str = '/***************************************'
                    + '\n Copyright: 2016, '
                    + '\n FileName: ' + fileName
                    + '\n Description: '
                    + '\n Author: '
                    + '\n Version: '
                    + '\n Date: ' + date.format('yyyy-MM-dd hh:mm:ss')
                    + '\n History: '
                    + '\n****************************************/'
                    + '\n';
                break;
            case 'py':
                str = '##############################################################'
                    + '\n#'
                    + '\n# Copyright (c) 2016 ranlau.com All Rights Reserved'
                    + '\n#'
                    + '\n############################################################'
                    + '\n"""'
                    + '\nthis is the description'
                    + '\nAuthors:'
                    + '\nDate: ' + date.format('yyyy-MM-dd hh:mm:ss')
                    + '\n"""'
                    + '\n';
                break;
        }
        return str;
    }

    return {
        codes: codes,
        idMap: idMap,
        typeMap: typeMap,
        isEditFileName: isEditFileName,
        editorMode: editorMode,
        setComment: setComment,
    };
});