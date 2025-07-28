/**
 * Search Protection Admin JavaScript
 * Handles cache clearing and FULLTEXT index management
 */

jQuery(document).ready(function($) {
    // Cache clearing functionality
    function clearCache(clearAll) {
        var button = $('#clear-search-cache');
        var status = $('#clear-cache-status');
        
        if (!clearAll) {
            var clearSearchResults = $('#clear-search-results').is(':checked');
            var clearRecentSearches = $('#clear-recent-searches').is(':checked');
            var clearRateLimits = $('#clear-rate-limits').is(':checked');
            var clearDetailedLogs = $('#clear-detailed-logs').is(':checked');
            
            if (!clearSearchResults && !clearRecentSearches && !clearRateLimits && !clearDetailedLogs) {
                status.html('<span class="fau-status-error">✗ Please select at least one option to clear.</span>');
                return;
            }
        }
        
        button.prop('disabled', true).text('Clearing...');
        $('#clear-all-cache').prop('disabled', true);
        status.html('<span class="fau-status-info">Clearing cache...</span>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'clear_search_cache',
                nonce: fauSearchProtection.nonce,
                clear_all: clearAll ? 1 : 0,
                clear_search_results: clearAll ? 1 : ($('#clear-search-results').is(':checked') ? 1 : 0),
                clear_recent_searches: clearAll ? 1 : ($('#clear-recent-searches').is(':checked') ? 1 : 0),
                clear_rate_limits: clearAll ? 1 : ($('#clear-rate-limits').is(':checked') ? 1 : 0),
                clear_detailed_logs: clearAll ? 1 : ($('#clear-detailed-logs').is(':checked') ? 1 : 0)
            },
            success: function(response) {
                if (response.success) {
                    status.html('<span class="fau-status-success">✓ ' + response.data.message + '</span>');
                    button.text('Cache Cleared').addClass('button-primary');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    status.html('<span class="fau-status-error">✗ ' + response.data.message + '</span>');
                    button.prop('disabled', false).text('Retry');
                    $('#clear-all-cache').prop('disabled', false);
                }
            },
            error: function() {
                status.html('<span class="fau-status-error">✗ AJAX request failed. Please try again.</span>');
                button.prop('disabled', false).text('Retry');
                $('#clear-all-cache').prop('disabled', false);
            }
        });
    }
    
    // Clear selected cache button
    $('#clear-search-cache').on('click', function() {
        clearCache(false);
    });
    
    // Clear all cache button
    $('#clear-all-cache').on('click', function() {
        // Clear all cache regardless of checkbox states
        var button = $(this);
        var status = $('#clear-cache-status');
        
        button.prop('disabled', true).text('Clearing All...');
        $('#clear-search-cache').prop('disabled', true);
        status.html('<span class="fau-status-info">Clearing all cache...</span>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'clear_search_cache',
                nonce: fauSearchProtection.nonce,
                clear_all: 1,
                clear_search_results: 1,
                clear_recent_searches: 1,
                clear_rate_limits: 1,
                clear_detailed_logs: 1
            },
            success: function(response) {
                if (response.success) {
                    status.html('<span class="fau-status-success">✓ ' + response.data.message + '</span>');
                    button.text('All Cache Cleared').addClass('button-secondary');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    status.html('<span class="fau-status-error">✗ ' + response.data.message + '</span>');
                    button.prop('disabled', false).text('Clear All Cache');
                    $('#clear-search-cache').prop('disabled', false);
                }
            },
            error: function() {
                status.html('<span class="fau-status-error">✗ AJAX request failed. Please try again.</span>');
                button.prop('disabled', false).text('Clear All Cache');
                $('#clear-search-cache').prop('disabled', false);
            }
        });
    });
    
    // FULLTEXT index creation
    $('#create-fulltext-index').on('click', function() {
        var button = $(this);
        var status = $('#create-index-status');
        
        button.prop('disabled', true).text('Creating...');
        status.html('<span class="fau-status-info">Creating FULLTEXT index...</span>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'create_fulltext_index',
                nonce: fauSearchProtection.fulltextNonce
            },
            success: function(response) {
                if (response.success) {
                    status.html('<span class="fau-status-success">✓ ' + response.data.message + '</span>');
                    button.text('Index Created').addClass('button-secondary');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    status.html('<span class="fau-status-error">✗ ' + response.data.message + '</span>');
                    button.prop('disabled', false).text('Retry');
                }
            },
            error: function() {
                status.html('<span class="fau-status-error">✗ AJAX request failed. Please try again.</span>');
                button.prop('disabled', false).text('Retry');
            }
        });
    });
}); 