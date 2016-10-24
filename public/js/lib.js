/**
 * 工具 信息提示框,信息检查工具
 */
define(['config', 'base'], function (config, base) {
    Date.prototype.format = function (format) {
        var o = {
            'M+': this.getMonth() + 1,
            'd+': this.getDate(),
            'h+': this.getHours(),
            'm+': this.getMinutes(),
            's+': this.getSeconds(),
            'q+': Math.floor((this.getMonth() + 3) / 3),
            'S': this.getMilliseconds()
        };

        if (/(y+)/.test(format)) {
            format = format.replace(RegExp.$1, (this.getFullYear() + '').substr(4 - RegExp.$1.length));
        }

        for (var k in o) {
            if (new RegExp('(' + k + ')').test(format)) {
                format = format.replace(RegExp.$1, RegExp.$1.length === 1
                    ? o[k] : ('00' + o[k]).substr(('' + o[k]).length));
            }
        }
        return format;
    };

    function checkFileName(fileName) {
        var ret = {
            code: 0,
            msg: '',
            fileName: '',
        };
        if ($.trim(fileName) === '') {
            ret.code = 1;
            ret.msg = '请填写文件名';
            return ret;
        }

        var ext = fileName.split('.').pop();
        // 根据选择的语言类型进行验证
        var lang = $('#lang').val();
        if (lang !== ext) {
            // ret.code = 2;
            // ret.msg = '文件后缀必须为' + lang + ',例如tmp.' + lang;
            fileName = fileName + '.' + lang;
            ret.fileName = fileName;
        }

        return ret;
    }

    // 获取文件数量
    function getFileNum(obj) {
        var n = 0;
        for (var key in obj) {
            if (obj.hasOwnProperty(key)) {
                n++;
            }
        }
        return n;
    }

    return {
        showMsg: base.showMsg,
        checkFileName: checkFileName,
        getFileNum: getFileNum,
    };
});
