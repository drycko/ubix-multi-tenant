// use tooltips only if title attribute is present
// use auto-hide alerts after 5 seconds
// roles and permissions js functions

$(document).ready(function() {
    // Initialize tooltips
    // if ($('[title]').length) {
    //     $('[title]').tooltip();
    // }

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

$(document).ready(function() {
    updatePermissionSummary();
    
    // Update summary when permissions change
    $('.permission-checkbox').change(function() {
        updatePermissionSummary();
    });

    // select all permissions
    $('#select-all-btn').click(function() {
        selectAllPermissions();
    });

    // clear all permissions
    $('#clear-all-btn').click(function() {
        clearAllPermissions();
    });

    // select group permissions
    $('.select-group-btn').click(function() {
        const group = $(this).data('group');
        selectGroupPermissions(group);
    });

    // clear group permissions
    $('.clear-group-btn').click(function() {
        const group = $(this).data('group');
        clearGroupPermissions(group);
    });
});

function selectAllPermissions() {
    $('.permission-checkbox').prop('checked', true);
    updatePermissionSummary();
}

function clearAllPermissions() {
    $('.permission-checkbox').prop('checked', false);
    updatePermissionSummary();
}

function selectGroupPermissions(group) {
    $(`.permission-checkbox[data-group="${group}"]`).prop('checked', true);
    updatePermissionSummary();
}

function clearGroupPermissions(group) {
    $(`.permission-checkbox[data-group="${group}"]`).prop('checked', false);
    updatePermissionSummary();
}

function resetToOriginal() {
    // Uncheck all first
    $('.permission-checkbox').prop('checked', false);
    
    // Check original permissions
    originalPermissions.forEach(function(permissionId) {
        $(`#permission_${permissionId}`).prop('checked', true);
    });
    
    updatePermissionSummary();
}

function updatePermissionSummary() {
    const selectedCount = $('.permission-checkbox:checked').length;
    const totalCount = $('.permission-checkbox').length;
    const percentage = totalCount > 0 ? Math.round((selectedCount / totalCount) * 100) : 0;
    
    $('#selected-count').text(selectedCount);
    $('#total-count').text(totalCount);
    $('#permission-progress').css('width', percentage + '%');
    $('#progress-text').text(percentage + '%');
    
    // Update progress bar color based on percentage
    const progressBar = $('#permission-progress');
    progressBar.removeClass('bg-danger bg-warning bg-success');
    
    if (percentage < 25) {
        progressBar.addClass('bg-danger');
    } else if (percentage < 75) {
        progressBar.addClass('bg-warning');
    } else {
        progressBar.addClass('bg-success');
    }
}