/**
 * tester
 */
define(['lib', 'config', 'tab'], function (lib, config, tab) {
    var showMsg = lib.showMsg;
    var isEditFileName = config.isEditFileName;

    var proId = $('#pro-id').val();
    var proName = $('#pro-name').val();
    var fileType = $('#lang').val();

    $.post('index.php?&a=getCodes', {proId: proId}, function (data) {
        data = JSON.parse(data);
        config.nameMap = data.nameMap;
        // 文件名后面是否有*
        var isChange = true;
        if (lib.getFileNum(config.nameMap) === 0) {
            var fileName = config.editorMode[fileType]['fileName'];
            var content = config.setComment(fileName, fileType);
            config.nameMap[fileName] = {
                codes: content,
                type: fileType,
                name: fileName,
                id: 0,
                fullName: fileName,
            };
            // config.nameMap[fileName].codes = content;
            isChange = true;
        } else {
            isChange = false;
        }
        // 初始化browser
        tab.initBrowser(data.browser);
        // 根据codes来渲染tab列表
        tab.setTabList(config.nameMap, isChange);
    });

    $('#type-msg').popover({trigger: 'hover'});
    // 运行程序
    $('#btn-run').click(function () {
        $(this).button('loading');
        var lang = $('#pro-type').val();
        $code = tab.editor.getValue();
        var codes = {};
        for (var k in config.nameMap) {
            if (config.nameMap.hasOwnProperty(k)) {
                codes[k] = config.nameMap[k].codes;
            }
        }
        switch (lang) {
            case 'html':
            case 'php':
            case 'c':
                $.post(
                    'index.php?a=runCode',
                    {codes: JSON.stringify(codes), proId: proId, proName: proName, type: lang},
                    function (ret) {
                        ret = JSON.parse(ret);
                        if (+ret.code === 0) {
                            if (lang === 'c') {
                                window.open('http://123.56.144.238:' + ret.port + '/?ssh=ssh://root@localhost/');
                            } else {
                                window.open(ret.data);
                            }
                        } else if (+ret.code === 1) {
                            lib.showMsg(ret.msg);
                        }
                        $('#btn-run').button('reset');
                    }
                );
                break;
        }
    });

    // 放大
    $('#modal-zoom').hide();
    $('#btn-zoom').click(function () {
        $('#modal-zoom').show('fast'); // 要先显示再进行下面的操作
        tab.editorZoom.setOption('value', tab.editor.getValue());
        $('#modal-zoom').find('.CodeMirror').css('height', $(window).height() + 'px');
        tab.editorZoom.focus();
    });

    return {
    };
});
