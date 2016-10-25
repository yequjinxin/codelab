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
        var lang = $('#pro-type').val();
        $code = tab.editor.getValue();
        switch (lang) {
            case 'javascript':
                eval($code);
                break;
            case 'html':
            case 'php':
                var codes = {};
                for (var k in config.nameMap) {
                    if (config.nameMap.hasOwnProperty(k)) {
                        codes[k] = config.nameMap[k].codes;
                    }
                }
                $.post(
                    'index.php?&a=runPhp',
                    {codes: JSON.stringify(codes), proId: proId, proName: proName},
                    function (ret) {
                        window.open(ret);
                    }
                );
                break;
            case 'c':
                
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
