define([], function () {
    function showMsg(text) {
        $('#modal-msg .modal-body').text(text);
        $('#modal-msg').modal('show');
    }
    $('#btn-msg').click(function () {
        $('#modal-msg').modal('hide');
    });
    return {
        showMsg: showMsg,
    };
});