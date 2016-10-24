define(['lib', 'config', 'baseBrowser'], function (lib, config, baseBrowser) {
    $('#btn-refresh-dir').click(baseBrowser.renderBrowser);
    $('#btn-add-dir').click(function () {
        $('#modal-add-dir').modal('show');
    });
    $('#modal-add-dir .btn-cancel').click(function () {
        $('#modal-add-dir').modal('hide');
    });
    $('#modal-add-dir .btn-confirm').click(function () {
        var dirName = $('#dir-name').val();
        var proId = $('#pro-id').val();
        var parentId = baseBrowser.getParentId();
        // 添加目录
        $.post('index.php?&a=addDir', {proId: proId, dirName: dirName, parentId: parentId}, function (ret) {
            ret = JSON.parse(ret);
            if (+ret.code === 0) {
                baseBrowser.renderBrowser();
                lib.showMsg('目录添加成功');
            } else {
                lib.showMsg('目录添加失败');
            }
            $('#modal-add-dir').modal('hide');
        });
    });

    return {
        initBrowser: baseBrowser.initBrowser,
        renderBrowser: baseBrowser.renderBrowser,
        getParentId: baseBrowser.getParentId,
    };
});