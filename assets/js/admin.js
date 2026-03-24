jQuery(document).ready(function($) {
    // Simulate Visit
    $('#silent-simulate-visit').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).text('Simulating...');
        
        // Just a mock interaction for the UI redesign
        setTimeout(function() {
            alert('Visit simulated! New data will appear in the dashboard shortly.');
            $btn.prop('disabled', false).html('<span class="dashicons dashicons-visibility"></span> Simulate Visit');
        }, 1000);
    });

    // Modal Toggles
    $('#silent-open-builder').on('click', function() {
        $('#silent-builder-overlay').fadeIn(200);
    });

    $('.silent-modal-close').on('click', function() {
        $('#silent-builder-overlay').fadeOut(200);
    });

    // Close modal on escape key
    $(document).on('keydown', function(e) {
        if (e.key === "Escape") {
            $('#silent-builder-overlay').fadeOut(200);
        }
    });

    // V1 Toggle
    $('.sa-container').on('click', '.silent-toggle-btn', function() {
        const $btn = $(this);
        const data = {
            action: 'silent_toggle_automation',
            nonce: silentAdmin.nonce,
            rule_id: $btn.data('rule-id'),
            page_url: $btn.data('page-url'),
            type: $btn.data('type')
        };

        $btn.prop('disabled', true).text('...');

        $.post(silentAdmin.ajaxUrl, data, function(response) {
            if (response.success) {
                if (response.data.status === 'activated') {
                    $btn.removeClass('sa-btn-primary').addClass('sa-btn-secondary').text('Turn Off');
                } else {
                    $btn.removeClass('sa-btn-secondary').addClass('sa-btn-primary').text('Turn This On');
                }
            }
            $btn.prop('disabled', false);
        });
    });

    // V2 New Automation
    $('#silent-new-automation-form').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('button[type="submit"]');
        const data = $form.serialize() + '&action=silent_save_automation&nonce=' + silentAdmin.nonce;

        $btn.prop('disabled', true).text('Saving...');

        $.post(silentAdmin.ajaxUrl, data, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error saving automation');
                $btn.prop('disabled', false).text('Create Automation');
            }
        });
    });

    // V2 Delete Automation
    $('.silent-delete-auto').on('click', function() {
        if (!confirm('Are you sure you want to delete this automation?')) return;
        const id = $(this).data('id');
        const $btn = $(this);
        
        $btn.prop('disabled', true);

        $.post(silentAdmin.ajaxUrl, {
            action: 'silent_delete_automation',
            id: id,
            nonce: silentAdmin.nonce
        }, function() {
            location.reload();
        });
    });

    // V2 Settings
    $('#silent-settings-form').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const data = $(this).serialize() + '&action=silent_save_settings&nonce=' + silentAdmin.nonce;
        
        $btn.prop('disabled', true).text('Saving...');

        $.post(silentAdmin.ajaxUrl, data, function(response) {
            if (response.success) {
                alert('Settings saved successfully!');
            }
            $btn.prop('disabled', false).text('Save Settings');
        });
    });
});
