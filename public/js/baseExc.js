define(['base'], function (base) {
    $('#btn-msg').click(function () {
        $('#modal-msg').modal('hide');
    });
    $('#create-pro').click(function () {
        $('#modal-create-pro').modal('show');
    });
    $('#modal-btn-submit').click(function () {
        if ($.trim($('input[name="name"]').val()) === '') {
            base.showMsg('请填写项目名称');
            return
        }
        $('#form-project').submit();
    });
    $('#modal-btn-cancel').click(function () {
        $('#modal-create-pro').modal('hide');
    });
});