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

    // Initialize Chart
    if ($('#sa-activity-chart').length > 0 && typeof Chart !== 'undefined' && silentAdmin.chartData) {
        const ctx = document.getElementById('sa-activity-chart').getContext('2d');
        const labels = silentAdmin.chartData.map(d => d.date);
        const data = silentAdmin.chartData.map(d => d.count);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Events',
                    data: data,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#6366f1',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#fff',
                        bodyColor: '#cbd5e1',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { 
                            color: '#f1f5f9',
                            drawBorder: false
                        },
                        ticks: { 
                            stepSize: 1,
                            color: '#94a3b8',
                            font: { size: 11 }
                        }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { 
                            color: '#94a3b8',
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }

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
                    $btn.removeClass('sa-btn-primary').addClass('sa-btn-secondary').text('Deactivate');
                } else {
                    $btn.removeClass('sa-btn-secondary').addClass('sa-btn-primary').text('Activate Automation');
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

        $btn.prop('disabled', true).text('Processing...');

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
        if (!confirm('Are you sure you want to delete this automation? This action cannot be undone.')) return;
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
                // Show a subtle toast or just update button
                $btn.text('Settings Updated!');
                setTimeout(() => {
                    $btn.prop('disabled', false).text('Update Settings');
                }, 2000);
            } else {
                $btn.prop('disabled', false).text('Update Settings');
            }
        });
    });
});
