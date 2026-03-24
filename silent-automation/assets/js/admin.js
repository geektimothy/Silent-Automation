jQuery(document).ready(function($) {
    $('.silent-toggle-btn').on('click', function() {
        const $btn = $(this);
        const data = {
            action: 'silent_toggle_automation',
            nonce: silentAdmin.nonce,
            rule_id: $btn.data('rule-id'),
            page_url: $btn.data('page-url'),
            type: $btn.data('type')
        };

        $btn.prop('disabled', true).text('Processing...');

        $.post(silentAdmin.ajaxUrl, data, function(response) {
            if (response.success) {
                if (response.data.status === 'activated') {
                    $btn.removeClass('button-primary').addClass('button-secondary').text('Deactivate');
                } else {
                    $btn.removeClass('button-secondary').addClass('button-primary').text('Activate Automation');
                }
            } else {
                alert('Error: ' + response.data);
            }
            $btn.prop('disabled', false);
        });
    });
});
