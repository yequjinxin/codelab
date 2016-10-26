define(['base'], function (base) {
    $('#btn-msg').click(function () {
        $('#modal-msg').modal('hide');
    });
    $('#create-pro').click(function () {
        $('#modal-create-pro').modal('show');
    });
    $('#modal-btn-submit').click(function () {
        var proName = $.trim($('input[name="name"]').val());
        var mode = /[-,.?:;'"!`，。？：；‘“！·\s]/;
        if (proName === '' || proName.length > 32 || mode.exec(proName)) {
            base.showMsg('项目名称不能为空;不大于32个字符;不能包含标点符号、空格!');
            return;
        }
        $(this).button('loading');
        $('#form-project').submit();
    });
    $('#modal-btn-cancel').click(function () {
        $('#modal-create-pro').modal('hide');
    });
});