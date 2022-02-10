$(document).ready(function () {
    // special behavior for terms checkbox
    $('.siddata-terms-form input:checkbox').click(function (e) {
        if ($(this).prop('checked')) {
            $('.siddata-terms-form button').prop('disabled', false);
        } else {
            $('.siddata-terms-form button').prop('disabled', true);
        }
    });
});
