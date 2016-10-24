define([], function () {
    function getParentId() {
        var dir = $('#browser').treeview('getSelected');
        var parentId = 0;
        // 如果选中的是文件,则选择文件的parentId
        if (dir[0] && dir[0].fileId) {
            if (dir[0].type === 'dir') {
                parentId = dir[0].fileId;
            } else {
                parentId = dir[0].parentId;
            }
        }
        return parentId;
    }
    function initBrowser(data) {
        $('#browser').treeview({
            data: data,
            multiSelect: $('#chk-select-multi').is(':checked'),
            onNodeSelected: function(event, node) {
                var fileId = node.fileId;
                for (var name in config.idMap) {
                    if (+config.idMap[name] === +fileId) {
                        $('#code-tab').find('li').each(function () {
                            if ($(this).find('.file-name').text() === name) {
                                // tab.setTab($(this));
                                
                            }
                        });
                        break;
                    }
                }
            },
            onNodeUnselected: function (event, node) {
              // $('#selectable-output').prepend('<p>' + node.text + ' was unselected</p>');
                // console.log(node);
            }
        });
    }

    function renderBrowser() {
        var proId = $('#pro-id').val();
        $.post('index.php?&a=browser', {proId: proId}, function (ret) {
            ret = JSON.parse(ret);
            initBrowser(ret.browser);
        });
    }
    return {
        getParentId: getParentId,
        initBrowser: initBrowser,
        renderBrowser: renderBrowser,
    };
});