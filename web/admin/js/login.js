// form submission
$('form').submit(function(e) {
    e.preventDefault ();
    $('.warning').removeClass('warning');
    $('input[type="submit"]')
            .attr('disabled', 'disabled')
            .parent()
            .removeClass('primary')
            .addClass('default');
    var params = $(this).serialize();
    var showLoader = setTimeout(function(){
        $('.alert').removeClass('danger hidden').addClass('default').html('Wird geladen...');
    }, 300);
    $.post("./api/authenticate.php", params).always(function(data) {
        data = data || {};
        clearTimeout(showLoader);
        $('input[type="submit"]')
                .attr('disabled', false)
                .parent()
                .removeClass('default')
                .addClass('primary');
        if (data.success) {
            location.reload();
        } else {
            if (data.warning) {
                $.each (data.warning, function(i){
                    setTimeout(function(){
                        $(data.warning[i]).addClass('warning');
                    }, 300);
                });
            }
            if (data.error || !data.warning) {
                $('.alert').removeClass('default hidden').addClass('danger').html(data.error || 'Login fehlgeschlagen!')
            } else {
                $('.alert').addClass('hidden');
            }
        }
    });
});
