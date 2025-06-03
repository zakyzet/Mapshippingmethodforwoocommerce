<?php
/**
 * Plugin Name: Bluebird Shipping
 * Description: Perhitungan ongkir berdasarkan jarak (per km) dengan map marker.
 * Version: 1.1
 * Author: iqsa
 */

if (!defined('ABSPATH')) exit;

// Tunggu sampai semua plugin aktif, lalu load class WooCommerce
add_action('plugins_loaded', function () {
    if (!class_exists('WC_Shipping_Method')) return;

    require_once plugin_dir_path(__FILE__) . 'includes/class-bluebirdshipping.php';

    add_action('woocommerce_shipping_init', function () {
        require_once plugin_dir_path(__FILE__) . 'includes/class-bluebirdshipping.php';
    });

    add_filter('woocommerce_shipping_methods', function ($methods) {
        $methods['bluebird_shipping'] = 'WC_Bluebird_Shipping_Method';
        return $methods;
    });
});
