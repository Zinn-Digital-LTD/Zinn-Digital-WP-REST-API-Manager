
jQuery(function($){
    $('.zinn-rest-toggle').on('click', function(e){
        e.preventDefault();
        var btn = $(this);
        var key = btn.data('key');
        var section = btn.data('section');
        var type = btn.data('type');
        var exposed = btn.data('exposed');
        btn.prop('disabled', true).text('Processing...');
        $.post(zinnwpapi.ajaxurl, {
            action: 'zinn_toggle_rest_status',
            key: key,
            section: section,
            type: type,
            exposed: exposed,
            _ajax_nonce: zinnwpapi.nonce
        }, function(resp){
            alert('REST API status updated!');
            window.location.reload();
        });
    });
});
