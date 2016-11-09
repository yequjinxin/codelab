/**
 * tester
 */
define(['lib', 'config', 'tab'], function (lib, config, tab) {
    var showMsg = lib.showMsg;
    var isEditFileName = config.isEditFileName;

    var proId = $('#pro-id').val();
    var proName = $('#pro-name').val();
    var fileType = $('#lang').val();
    var containerSrc = '';

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
                                containerSrc = 'http://123.56.144.238:' + ret.port + '/?ssh=ssh://root@localhost/';
                                $('#div-run iframe').attr('src', containerSrc);
                                setTimeout(function () {
                                    containSwitch(false);
                                    $('#btn-run').button('reset');
                                    $('#btn-run').hide();
                                    $('#btn-stop').show();
                                    $('#div-btn-run').show();
                                }, 2000);
                            } else {
                                containerSrc = ret.data;
                                $('#div-run iframe').attr('src', containerSrc);
                                $('#btn-run').button('reset');
                            }
                        } else {
                            lib.showMsg(ret.msg);
                        }
                    }
                );
                break;
        }
    });

    $('#btn-stop').click(function () {
        $(this).button('loading');
        containSwitch(true);
        $('#div-btn-run').hide();
        var proId = $('#pro-id').val();
        var proName = $('#pro-name').val();
        $.post('index.php?a=stopContainer', {proId, proId, proName: proName}, function (ret) {
            ret = JSON.parse(ret);
            if (+ret.code === 0) {
                $('#btn-stop').button('reset');
                $('#btn-stop').hide();
                $('#btn-run').show();
            } else {
                lib.showMsg(ret.msg);
            }
        });
    });

    $(window).bind('beforeunload', function () {
        $.post('index.php?a=stopContainer', {proId, proId, proName: proName});
    });

    // 放大
    $('#modal-zoom').hide();
    $('#btn-zoom').click(function () {
        $('#modal-zoom').show('fast'); // 要先显示再进行下面的操作
        tab.editorZoom.setOption('value', tab.editor.getValue());
        $('#modal-zoom').find('.CodeMirror').css('height', $(window).height() + 'px');
        tab.editorZoom.focus();
    });

    // 容器
    function getSwitch() {
        var flag = false;
        return function () {
            flag = !flag;
            return flag;
        }
    }
    function containSwitch(flag) {
        if (flag) {
            $('#div-run').hide();
            $('#div-code').attr('class', 'col-lg-10');
            $('#btn-container-switch span').attr('class', 'glyphicon glyphicon-chevron-left');
        } else {
            $('#div-run').show();
            $('#div-code').attr('class', 'col-lg-6');
            $('#btn-container-switch span').attr('class', 'glyphicon glyphicon-chevron-right');
        }
    }
    var flagFunc = getSwitch();
    $('#btn-container-switch').click(function () {
        var flag = flagFunc();
        containSwitch(flag);
    });
    $('#btn-container-max').click(function () {
        window.open(containerSrc);
    });
    containSwitch(true);

    $('#btn-container-fresh').click(function () {
        $('#div-run iframe').remove();
        var iframe = $('<iframe src="' + containerSrc + '" scrolling="no" frameborder="1"></iframe>');
        $('#div-run').append(iframe);
    });

    return {
    };
});
