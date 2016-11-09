/**
 * tab
 */
define(['config', 'lib'], function (config, lib) {
    // editor 开始
    // 标尺
    var rulers = [{
        color: '#fcc',
        column: 120,
        lineStyle: 'dashed',
    }];
    function createMirror(id) {
        var mirror = CodeMirror.fromTextArea(document.getElementById(id), {
            lineNumbers: true,
            mode: "javascript",
            // mode: 'text/html',
            // keyMap: "sublime",
            keyMap: 'vim', // vim编辑模式
            autoCloseBrackets: true, // 自动关闭括号
            styleActiveLine: true, // 行高亮显示
            matchBrackets: true, // 光标靠近括号自动匹配对应的括号
            showCursorWhenSelecting: true, // 在选中时是否显示光标
            matchTags: {bothTags: true}, // 成对匹配html标签
            // extraKeys: {"Ctrl-J": "toMatchingTag"}, // 查找成对的html标签
            autoCloseTags: true, // 自动关闭html标签
            indentUnit: 4, // 缩进4个空格
            indentWithTabs: false, // 用tab缩进
            autofocus: true, // 自动聚焦
            smartIndent: true, // 智能缩进
            // theme: "monokai",
            theme: 'mdn-like',
            rulers: rulers, // 标尺线
            specialChars: /[\u0000-\u001f\u007f\u00ad\u200b-\u200f\u2028\u2029\ufeff\u0020]/,
            specialCharPlaceholder: function (ch) {
                var e = document.createElement('span');
                e.className = 'cm-invalidchar1';
                e.appendChild(document.createTextNode('\u002E'));
                e.title = "\\u" + ch.charCodeAt(0).toString(16);
                e.setAttribute("aria-label", e.title);
                return e;
            },
        });
        return mirror;
    }
    var editor = createMirror('code');
    var editorZoom = createMirror('codeZoom');
    // 设置按键
    editor.setOption("extraKeys", {
        Tab: function(cm) {
          var spaces = Array(cm.getOption("indentUnit") + 1).join(" ");
          cm.replaceSelection(spaces);
        },
        'Ctrl-J': 'toMatchingTag',
        'Ctrl-S': function (cm) {
            // 保存当前文件
            var fileName = $('#code-tab li.active a .file-name').data('fileName');
            var id = 0;
            if (config.nameMap[fileName]) {
                id = config.nameMap[fileName].id;
            }
            var name = '';
            if (config.nameMap[fileName]) {
                name = config.nameMap[fileName].name;
            }
            var node = $('#browser').treeview('getSelected');
            /*
            if (id && name !== node[0].text) {
                if (!confirm('确定移动当前文件到？' + node[0].text)) {
                    return;
                }
            }
            */
            var fileType = $('#lang').val();
            if (config.nameMap[fileName]) {
                fileType = config.nameMap[fileName].type;
            }
            var content = editor.getValue();
            var proId = $('#pro-id').val();
            var proName = $('#pro-name').val();
            var parentId = getParentId();

            $('.mylayer span').text('页面保存中...');
            $('.mylayer').show();
            $.post(
                'index.php?&a=saveCode',
                {id: id, fileName: name, proId: proId,parentId: parentId, type: fileType, content: content, proName: proName},
                function (ret) {
                    ret = JSON.parse(ret);
                    $('.mylayer').hide();
                    if (+ret.code === 0) {
                        // 去掉文件名后面的*
                        $('#code-tab li.active a .save-flag').hide();

                        // 更新config.nameMap和data('fileName')的值
                        var fullPath = name;
                        var path = getParentText();
                        if (path) {
                            fullPath =  path + '/' + name;
                        }
                        if (fullPath !== fileName) {
                            config.nameMap[fullPath] = config.nameMap[fileName];
                            $('#code-tab').find('.active a .file-name').data('fileName', fullPath);
                            delete config.nameMap[fileName];
                        }

                        // 如果是添加
                        if (!id) {
                            config.nameMap[fullPath].id = ret.id;
                        }

                        // 重新渲染browser
                        renderBrowser();
                    }
                }
            );
        },
    });

    editorZoom.setOption('extraKeys', {
        // 关闭editorZoom时同步数据
        Esc: function (cm) {
            $('#modal-zoom').hide();
            editor.setOption('value', editorZoom.getValue());
            editor.focus();
        }
    });
    var tmpFileName = '';
    // 编辑器内容变化触发事件,保存进内存
    editor.on('changes', function () {
        // 文件名key
        var key = $('#code-tab li.active a .file-name').data('fileName');
        if (key) {
            if (typeof config.nameMap[key] !== 'undefined') {
                config.nameMap[key].codes = editor.getValue();
            } else {
                throw 'err';
            }
            // 控制文件是否改变的*的显示
            if (key === tmpFileName) {
                $('#code-tab li.active a .save-flag').show();
            } else {
                tmpFileName = key;
            }
        }
    });
    // editor 结束

    // browser 开始
    // $('#btn-refresh-dir').click(renderBrowser);
    // 添加目录
    $('#btn-add-dir').click(function () {
        $('#dir-name').data('fileId', 0);
        $('#dir-name').val('');
        $('#modal-add-dir .modal-title').text('添加目录');
        $('#modal-add-dir').modal('show');
    });
    $('#modal-add-dir .btn-cancel').click(function () {
        $('#modal-add-dir').modal('hide');
    });
    $('#modal-add-dir .btn-confirm').click(function () {
        var dirName = $('#dir-name').val();
        if (!$.trim(dirName)) {
            lib.showMsg('目录不能为空');
            return;
        }
        var fileId = $('#dir-name').data('fileId')
        if (fileId) {
            // update
            var name = dirName;
            $.post('index.php?a=updateFileName', {id: fileId, name: name}, function (ret) {
                ret = JSON.parse(ret);
                if (+ret.code === 0) {
                    var activeTab = $('#code-tab li.active').find('a .file-name');
                    var oldFullPath = activeTab.data('fileName');
                    var oldName = config.nameMap[oldFullPath].name;
                    var fullPath = oldFullPath.replace(oldName, name);

                    // 更新text
                    activeTab.text(name);
                    // 更新data
                    activeTab.data('fileName', fullPath);
                    // 更新config.nameMap
                    config.nameMap[fullPath] = config.nameMap[oldFullPath];
                    config.nameMap[fullPath].name = name;
                    config.nameMap[fullPath].fullPath = fullPath;
                    delete config.nameMap[oldFullPath];

                    renderBrowser(fileId);
                } else {
                    lib.showMsg(ret.msg);
                }
                $('#modal-add-dir').modal('hide');
            });
        } else {
            // add
            var proId = $('#pro-id').val();
            var parentId = getParentId();
            // 添加目录
            $.post('index.php?&a=addDir', {proId: proId, dirName: dirName, parentId: parentId}, function (ret) {
                ret = JSON.parse(ret);
                if (+ret.code === 0) {
                    renderBrowser(ret.id);
                    // lib.showMsg('目录添加成功');
                } else {
                    lib.showMsg('目录添加失败');
                }
                $('#modal-add-dir').modal('hide');
            });
        }
    });
    // 修改文件名
    $('#btn-edit-file').click(function () {
        $('#modal-add-dir .modal-title').text('修改文件名');
        var file = $('#browser').treeview('getSelected');
        // -1 用来判断这是一个项目节点(首节点)
        if (!file[0] || +file[0].fileId === -1) {
            return;
        } else {
            dirName = $('#dir-name').val(file[0].text);
            $('#dir-name').data('fileId', file[0].fileId);
            $('#modal-add-dir').modal('show');
        }
    });
    // 删除文件
    $('#btn-del-file').click(function () {
        var file = $('#browser').treeview('getSelected');
        // 没有选中文件,无操作
        if (!file[0] || +file[0].fileId === -1) {
            return;
        } else {
            var tmpText = '';
            if (file[0].type === 'dir') {
                tmpText = '删除目录' + file[0].text + '以及目录中所有文件?';
            } else {
                tmpText = '删除文件' + file[0].text;
            }
            $('#modal-del-file label').text(tmpText);
            $('#modal-del-file').modal('show');
        }
    });
    $('#modal-del-file .btn-cancel').click(function () {
        $('#modal-del-file').modal('hide');
    });
    function initNameMap() {
        var fileType = $('#lang').val();
        var fileName = config.editorMode[fileType]['fileName'];
        var content = config.setComment(fileName, fileType);
        config.nameMap = {};
        config.nameMap[fileName] = {
            codes: content,
            type: fileType,
            name: fileName,
            id: 0,
            fullName: fileName,
        };
    }
    $('#modal-del-file .btn-confirm').click(function () {
        var file = $('#browser').treeview('getSelected');
        $.post('index.php?&a=delFile', {id: file[0].fileId, type: file[0].type}, function (ret) {
            ret = JSON.parse(ret);
            if (+ret.code === 0) {
                if (file[0].type !== 'dir') {
                    var activeTab = $('#code-tab li.active');
                    var fileName = activeTab.find('a .file-name').data('fileName');
                    var nextTab = activeTab.prev();

                    if (!nextTab.length) {
                        nextTab = activeTab.next();
                    }
                    // 删除当前选中tab的值和元素
                    delete config.nameMap[fileName];
                    activeTab.remove();

                    if (lib.getFileNum(config.nameMap) === 0) {
                        // 只剩下当前一个文件
                        initNameMap();
                        var isChange = true;
                        setTabList(config.nameMap, isChange);
                    } else {
                        // 选中下一个tab
                        setTab(nextTab);
                    }

                    // 刷新browser
                    renderBrowser();

                    // 本来想通过js控制tab的显示(体验很差)
                    // window.location.reload();
                } else {
                    var proId = $('#pro-id').val();
                    $.post('index.php?&a=getCodes', {proId: proId}, function (ret) {
                        ret = JSON.parse(ret);
                        initBrowser(ret.browser);

                        var isChange = true;
                        if (lib.getFileNum(ret.nameMap) === 0) {
                            initNameMap();
                            isChange = true;
                        } else {
                            config.nameMap = ret.nameMap;
                            isChange = false;
                        }
                        setTabList(config.nameMap, isChange);
                    });
                }
            } else {
                lib.showMsg(ret.msg);
            }
            $('#modal-del-file').modal('hide');
        });
    });

    // 获取父级文件的id
    function getParentId() {
        var dir = $('#browser').treeview('getSelected');
        var fatherId = 0;
        // 如果选中的是文件,则选择文件的parentId
        if (dir[0] && dir[0].fileId) {
            if (dir[0].type === 'dir') {
                fatherId = dir[0].fileId;
            } else {
                fatherId = dir[0].fatherId;
            }
        }
        return fatherId;
    }
    // 获取父级文件的路径名(全路径)
    function getParentText() {
        var node = $('#browser').treeview('getSelected');
        var text = '';
        if (node[0]) {
            if (node[0].type === 'dir') {
                text = node[0].fullPath;
            } else {
                // var nodeInfo = $('#browser').treeview('search', [dir[0].fatherId, {ignoreCase: false, exactMatch: true}, 'fileId']);
                var parentNode = $('browser').treeview('getParent', node[0]);
                if (parentNode[0]) {
                    text = nodeInfo[0].text;
                }
            }
        }
        return text;
    }
    
    // 给browser初始化
    function initBrowser(data) {
        $('#browser').treeview({
            showTags: true,
            data: data,
            multiSelect: false,
            onNodeSelected: function(event, node) {
                editor.focus();
                $('#browser').treeview('search', [node.fileId, {ignoreCase: false, exactMatch: true}, 'fileId']);
                var fileId = node.fileId;
                for (var name in config.nameMap) {
                    if (+config.nameMap[name].id === +fileId) {
                        $('#code-tab').find('li').each(function () {
                            if ($(this).find('.file-name').data('fileName') === name) {
                                var noUpBrowser = true;
                                setTab($(this), noUpBrowser);
                            }
                        });
                        break;
                    }
                }
            },
            onNodeUnselected: function (event, node) {
            }
        });
    }

    // 点击文件，保存文件，添加文件和添加目录时会刷新browser
    function renderBrowser(dirId) {
        var proId = $('#pro-id').val();
        $.post('index.php?&a=getCodes', {proId: proId}, function (ret) {
            ret = JSON.parse(ret);
            initBrowser(ret.browser);

            // 选中
            if (!dirId) {
                var key = $('#code-tab').find('.active a .file-name').data('fileName');
                if (config.nameMap[key]) {
                    var fileId = config.nameMap[key].id;
                } else {
                    return;
                }
            } else {
                fileId = dirId;
            }
            if (fileId) {
                updateBrowser(fileId);
            }
        });
    }
    
    // 选中browser
    function updateBrowser(fileId) {
        var nodeInfo = $('#browser').treeview('search', [fileId, {ignoreCase: false, exactMatch: true}, 'fileId']);
        if (nodeInfo[0]) {
            var nodeId = nodeInfo[0].nodeId;
            $('#browser').treeview('selectNode', [nodeId, {silent: true}]);
        }
    }
    // browser 结束

    // tab 开始
    // 点击tab控制active并同步显示editor的值
    function setTab(tab, noUpBrowser) {
        $('#code-tab').find('li').each(function () {
            $(this).removeClass('active');
        });
        if (tab !== null) {
            tab.addClass('active');
            var key = tab.find('a .file-name').data('fileName');
            var value = ' ';
            var fileType = 'php';
            var fileId = 0;
            if (typeof config.nameMap[key] !== 'undefined') {
                value = config.nameMap[key].codes;
                fileType = config.nameMap[key].type;
                fileId = config.nameMap[key].id;
            }
            editor.setOption('value', value);
            editor.setOption('mode', config.editorMode[fileType]['mode']);
            editor.focus();

            // 同步更新browser
            if (!noUpBrowser && fileId) {
                updateBrowser(fileId);
            }
        }
    }
    // 双击
    function dbSetTab(tab) {
        // 生成input
        var inputStr = ''
            + '<li>'
            +     '<input type="text" class="input-tab">'
            + '</li>';
        tab.html(inputStr);
    }

    function makeNewTab(fileName) {
        var aStr = ''
            + '<a href="javascript:void(0)">'
            +     '<span class="file-name">'
            +         fileName
            +     '</span>'
            +     '<span class="save-flag">*</span>'
            + '</a>';
        var tab = $('<li>', {class: 'active', click: function () {
            setTab($(this));
        }}).html(aStr);
        $('#code-add').before(tab);
        return tab;
    }

    // 添加tab
    var showMsg = lib.showMsg;
    $('#code-add').click(function () {
        // 防止多次未结束的添加
        if (config.isEditFileName) {
            return;
        }
        // 生成input
        var inputStr = ''
            + '<li>'
            +     '<input id="input-add" class="input-tab" type="text">'
            + '</li>';
        $('#code-add').before(inputStr);
        $('#input-add').keydown(function () {
            enterBlur({type: 'keydown'});
        });
        config.isEditFileName = true;

        // 给input绑定blur事件
        $('#input-add').blur(enterBlur).focus();
    });

    // input 的blur处理函数
    function enterBlur(e) {
        if (e.type === 'blur' || e.type === 'keydown' && (window.event.keyCode || window.event.which) === 13) {
            var input = $('#input-add');
            var fileName = input.val();

            // 验证文件名,如果为kong,则removeinput框
            var ret = lib.checkFileName(fileName);
            if (ret.code === 1) {
                $('#input-add').parent().remove();
                // 结束编辑
                config.isEditFileName = false;
                return;
            }

            // 文件名修正(没有后缀默认加上后缀)
            if (ret.fileName) {
                fileName = ret.fileName;
            }

            // 生成新文件在config.nameMap中的值
            var fullPath = fileName;
            var path = getParentText();
            if (path) {
                fullPath =  path + '/' + fileName;
            }
            // 判断文件名是否重复
            if (typeof config.nameMap[fullPath] !== 'undefined') {
                lib.showMsg('文件已存在');
                input.focus();
                return;
            }

            // 删除input,添加tab
            $('#input-add').parent().remove();
            setTab(null)
            var tab = makeNewTab(fileName);

            // 新文件的内容默认为commnet并同步设置editor的值,文件类型为下拉菜单中选中的类型
            var fileType = $('#lang').val();
            var comment = config.setComment(fileName, fileType)
            tab.find('a .file-name').data('fileName', fullPath);
            config.nameMap[fullPath] = {
                'codes': comment,
                'type': fileType,
                'name': fileName,
                'fullPath': fullPath,
                'id': 0,
            };
            editor.setOption('mode', config.editorMode[fileType]['mode']);
            editor.setOption('value', comment);
            editor.focus();
            // 编辑完毕
            config.isEditFileName = false;
        }
    }

    // 根据codes生成tab
    function setTabList(nameMap, isChange) {
        $('#code-tab li').each(function () {
            if ($(this).attr('id') !== 'code-add') {
                $(this).remove();
            }
        });
        for (var fileName in nameMap) {
            var tab = makeNewTab(nameMap[fileName].name);
            tab.find('a .file-name').data('fileName', fileName);
        }
        if (!isChange) {
            $('#code-tab .save-flag').hide();
        }
        // 初始显示第一个tab
        if ($('#code-tab li')[0]) {
            $('#code-tab li')[0].click();
        }
    }
    // tab 结束


    return {
        setTabList: setTabList,
        setTab: setTab,
        initBrowser: initBrowser,
        editor: editor,
        editorZoom: editorZoom,
    };
});