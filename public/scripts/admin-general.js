jQuery(function() {
    var showElement = function(name, tf) {
        if (!tf) {
            jQuery('#' + name + '-label').hide();
            jQuery('#' + name + '-element').hide();
        } else {
            jQuery('#' + name + '-label').show();
            jQuery('#' + name + '-element').show();
        }
    };

    var onChangeCache = function() {
        showElement('cacheMemcacheHost', false);
        showElement('cacheMemcachePort', false);
        showElement('cacheXcacheUser', false);
        showElement('cacheXcachePassword', false);
        showElement('cacheFile', false)

        switch (jQuery('#cacheBackend').val()) {
            case 'Zend_Cache_Backend_File':
            case 'Zend_Cache_Backend_Sqlite':
                showElement('cacheFile', true);
                break;

            case 'Zend_Cache_Backend_Xcache':
                showElement('cacheXcacheUser', true);
                showElement('cacheXcachePassword', true);
                break;

            case 'Zend_Cache_Backend_Libmemcached':
            case 'Zend_Cache_Backend_Memcached':
                showElement('cacheMemcacheHost', true);
                showElement('cacheMemcachePort', true);
                break;

            case 'Zend_Cache_Backend_Apc':
            default:
                break;
        }
    };

    onChangeCache();
    jQuery('#cacheBackend').change(onChangeCache);

    var missing = jQuery.parseJSON(jQuery('#missing').val());
    for (var i = 0; i < missing.length; i++) {
        jQuery('#cacheBackend option[value="' + missing[i] + '"]').attr('disabled', 'disabled');
    }

    jQuery('#cacheBackend option').each(function(i) {
        var grp = jQuery('<optgroup/>');
        switch (jQuery(this).text()) {
            case 'APC':
                grp.attr('label', 'Alternative PHP Cache');
                break;

            case 'File-based':
                grp.attr('label', 'Cache to the Filesystem (Not Recommended)');
                break;

            case 'SQLite':
                grp.attr('label', 'PHP SQLite v3 (Not Recommended)');
                break;

            case 'Memcache':
                grp.attr('label', 'PHP Memcache Extension');
                break;

            case 'Memcached':
                grp.attr('label', 'PHP Memcached Extension (Recommended)');
                break;

            case 'XCache':
                grp.attr('label', 'XCache Extension');
                break;

            default:
                grp.attr('label', 'Other');
                break;
        }
        jQuery(this).wrapAll(grp);
    });
});
