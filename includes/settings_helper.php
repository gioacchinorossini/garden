<?php
/**
 * System Settings Helper
 */

function get_system_settings() {
    $settings_file = __DIR__ . '/system_settings.json';
    $defaults = [
        'farm_map_view_mode' => 'crops', // 'crops' or 'homestead'
        'enable_homestead_switcher' => false, // false hides view switcher & homestead view from landowners
        'default_lease_price' => 110000.00
    ];
    if (file_exists($settings_file)) {
        $content = file_get_contents($settings_file);
        $data = json_decode($content, true);
        if (is_array($data)) {
            return array_merge($defaults, $data);
        }
    }
    return $defaults;
}

function save_system_settings($settings) {
    $settings_file = __DIR__ . '/system_settings.json';
    return file_put_contents($settings_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
