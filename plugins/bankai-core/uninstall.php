<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    return;
}
$delete_data = get_option('bankai_delete_data_on_uninstall', false);
if (!$delete_data) {
    return;
}
delete_option('bankai_core_settings');
delete_option('bankai_ai_keys');
