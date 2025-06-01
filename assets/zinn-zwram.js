$ = jQuery;
$(document).ready(function () {

    $zinnZwramTabs = $("#zinn_zwram_tabs").tabs();

    $('#zinn_zwram_tabs #submit').click(function (e) {
        $('.zinn_zwram_settings_container').addClass('wait');

        $.when(
            $.post('options.php', $('#zinn_zwram_form_post').serialize()),
            $.post('options.php', $('#zinn_zwram_form_user').serialize()),
            $.post('options.php', $('#zinn_zwram_form_comment').serialize()),
            $.post('options.php', $('#zinn_zwram_form_term').serialize())

        ).done(function(a1, a2, a3, a4) {
            $('.zinn_zwram_settings_container').removeClass('wait');
        });

        return false;
        e.preventDefault()
    })


    $('.uncheck_all').click(function (e) {
        uncheckAllStatus = $(this).attr('data-status');

        if (uncheckAllStatus == 0) {
            $(this).attr('data-status', 1);
        } else {
            $(this).attr('data-status', 0);
        }

        $(this).closest('form').find('input[type="checkbox"]').each(function () {
            if (uncheckAllStatus == 0) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
            }
        })
        return false;
        e.preventDefault()
    })


})
