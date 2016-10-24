/**
 * editor
 */
define(['config', 'baseBrowser'], function (config, browser) {
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
            $('.mylayer span').text('页面保存中...');
            $('.mylayer').show();
            // 保存当前文件
            var fileName = $('#code-tab li.active a .file-name').text();
            var id = config.idMap[fileName];
            id = id ? id : 0;
            var content = editor.getValue();
            var proId = $('#pro-id').val();
            var fileType = config.typeMap[fileName] ? config.typeMap[fileName] : $('#lang').val();
            var parentId = browser.getParentId();

            $.post(
                'index.php?&a=saveCode',
                {id: id, fileName: fileName, proId: proId,parentId: parentId, type: fileType, content: content},
                function (ret) {
                    $('.mylayer').hide();
                    // 去掉文件名后面的*
                    $('#code-tab li.active a .save-flag').hide();
                    browser.renderBrowser();
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
        var key = $('#code-tab li.active a .file-name').text();
        if (key) {
            if (typeof config.codes[key] !== 'undefined') {
                config.codes[key] = editor.getValue();
            }
            // 控制文件是否改变的*的显示
            if (key === tmpFileName) {
                $('#code-tab li.active a .save-flag').show();
            } else {
                tmpFileName = key;
            }
        }
    });

    return {
        editor: editor,
        editorZoom: editorZoom,
    };
});
