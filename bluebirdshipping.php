<?php
/**
 * Plugin Name: Bluebird Shipping for WooCommerce
 * Description: WooCommerce shipping method with distance-based cost using Leaflet map.
 * Version: 1.0
 * Author: Iqsa
 */

if (!defined('ABSPATH')) exit;

add_action('plugins_loaded', function () {
    
    if (!class_exists('WC_Shipping_Method')) {
        return;
    }
    
    
    add_action('after_setup_theme', function() {
        add_theme_support('woocommerce');
    });

 
    require_once plugin_dir_path(__FILE__) . 'includes/class-bluebirdshipping.php';

  
    add_action('woocommerce_shipping_init', function () {
        require_once plugin_dir_path(__FILE__) . 'includes/class-bluebirdshipping.php';
    });

    add_filter('woocommerce_shipping_methods', function ($methods) {
        $methods['bluebird_shipping'] = 'WC_Bluebird_Shipping_Method';
        return $methods;
    });
    
    
    add_action('woocommerce_after_order_notes', function() {
         global $wp;
        $current_url = home_url(add_query_arg(array(), $wp->request));
        $customer_id = get_current_user_id();
        $buserlat = "";
        $buserlong = "";
        $suserlat = "";
        $suserlong = "106.8";
         $slat = "-6.2";
        $slong = "106.8";
        if(isset($_GET['lang'])){
            $buserlat = sanitize_text_field($_GET['lat']);
            $buserlong = sanitize_text_field($_GET['lang']);
            $suserlat = sanitize_text_field($_GET['lat']);
            $suserlong = sanitize_text_field($_GET['lang']);
            update_user_meta($customer_id, 'billing_latitude', $buserlat);
            update_user_meta($customer_id, 'billing_longitude', $buserlong);
            update_user_meta($customer_id, 'shipping_latitude', $suserlat);
            update_user_meta($customer_id, 'shipping_longitude', $suserlong);
            wp_safe_redirect(wc_get_checkout_url());
            exit;
        }


       global $wp;
        $current_url = home_url(add_query_arg(array(), $wp->request));
        $customer_id = get_current_user_id();
        $slat = get_user_meta($customer_id, 'shipping_latitude', true);
        $slong = get_user_meta($customer_id, 'shipping_longitude', true);
        ?>
        <div class="woocommerce-additional-fields__field-wrapper">
        <h3>Pilih Lokasi Pengiriman</h3>
        <div id="shipping-map" style="height: 300px; margin-bottom: 20px;"></div>
        
        
        
        <input type="text" readonly name="destination_lat" id="destination_lat" value="<?php echo $slat;?>" />
        <input type="text" readonly name="destination_lng" id="destination_lng" value="<?php echo $slong;?>" />
     <a href="<?php echo $current_url;?>" id="bsimpan" class="button">Simpan Pengiriman</a>
        
        </div>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var map = L.map('shipping-map').setView([<?php echo $slat;?>, <?php echo $slong;?>], 11);
            var marker;
             if (marker) {
                    marker.setLatLng([<?php echo $slat;?>, <?php echo $slong;?>]);
                } else {
                    marker = L.marker([<?php echo $slat;?>, <?php echo $slong;?>]).addTo(map);
                }
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);
    
            var marker;
            map.on('click', function (e) {
                var lat = e.latlng.lat;
                var lng = e.latlng.lng;
                document.getElementById('destination_lat').value = lat;
                document.getElementById('destination_lng').value = lng;
                document.getElementById('bsimpan').setAttribute("href", "<?php echo $current_url;?>/?lat="+lat+"&lang="+lng);
                
                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng).addTo(map);
                }
            });
        });
        
        function bsimpan(){
            alert('simpan');
        }
        </script>
        <?php
    });
    
    
    
});



add_action('woocommerce_after_checkout_shipping_form', function ($checkout) {
    ?>
 

    <?php
});



add_action('woocommerce_checkout_update_order_meta', 'save_shipping_coordinates');
function save_shipping_coordinates($order_id) {
    if (isset($_POST['destination_lat']) && isset($_POST['destination_lng'])) {
        update_post_meta($order_id, '_destination_lat', sanitize_text_field($_POST['destination_lat']));
        update_post_meta($order_id, '_destination_lng', sanitize_text_field($_POST['destination_lng']));
    }
}

add_action('admin_menu', function () {
    add_menu_page(
        'Bluebird Settings',
        'Bluebird Shipping',
        'manage_options',
        'bluebird-settings',
        'render_bluebird_settings_page',
        'dashicons-location-alt'
    );
});


function render_bluebird_settings_page() {
    $ilat = "";
    $bprice = "";
    
    if(isset($_POST['bluebird_origin_lat'])){
        update_option('origin_latitude', $_POST['bluebird_origin_lat']);
        update_option('origin_longutude', $_POST['bluebird_origin_lng']);
    }
    
    if(isset($_POST['bluebird_price'])){
        update_option('bluebird_price', $_POST['bluebird_price']);
        
    }
    
    
    $lat = get_option('origin_latitude');
    $lng = get_option('origin_longutude');
    
    $bprice = get_option('bluebird_price');
    ?>

     <h3>Set Origin Location </h3>
        <div id="shipping-map" style="height: 300px; margin-bottom: 20px;"></div>
        <input type="hidden" name="destination_lat" id="destination_lat" />
        <input type="hidden" name="destination_lng" id="destination_lng" />
    
    
        <form method="post" action="admin.php?page=bluebird-settings">
            <?php settings_fields('bluebird_settings'); ?>
            <?php do_settings_sections('bluebird_settings'); ?>

            <input type="text" name="bluebird_origin_lat" id="destination_lat2" value="<?php echo esc_attr($lat); ?>">
            <input type="text" name="bluebird_origin_lng" id="destination_lng2" value="<?php echo esc_attr($lng); ?>">

            <?php submit_button('Save Coordinate'); ?>
        </form>
        <hr>
     <h3>Set Price (/km)</h3>   
        <form method="post" action="admin.php?page=bluebird-settings">
            <?php settings_fields('bluebird_settings'); ?>
            <?php do_settings_sections('bluebird_settings'); ?>
            <label>Rp.</label>
            <input type="text" name="bluebird_price" id="price" value="<?php echo esc_attr($bprice); ?>">

            <?php submit_button('Save Price'); ?>
        </form>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const koordinat = {
                lat: <?= json_encode($lat); ?>,
                long: <?= json_encode($long); ?>
              };
            var map = L.map('shipping-map').setView([-6.2, 106.8], 11);
             var marker;
             if (marker) {
                    marker.setLatLng([<?php echo esc_attr($lat); ?>, <?php echo esc_attr($lng); ?>]);
                } else {
                    marker = L.marker([<?php echo esc_attr($lat); ?>, <?php echo esc_attr($lng); ?>]).addTo(map);
                }
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);
    
           
            map.on('click', function (e) {
                var lat = e.latlng.lat;
                var lng = e.latlng.lng;
                document.getElementById('destination_lat').value = lat;
                document.getElementById('destination_lng').value = lng;
                document.getElementById('destination_lat2').value = lat;
                document.getElementById('destination_lng2').value = lng;
                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng).addTo(map);
                }
            });
        });
        </script>
    <?php
}

add_filter('woocommerce_checkout_fields', 'tambah_lat_long_checkout');

function tambah_lat_long_checkout($fields) {
   
    $fields['billing']['billing_latitude'] = [
        'label'     => __('Latitude (Billing)', 'woocommerce'),
        'required'  => false,
        'class'     => ['form-row-first'],
        'clear'     => true,
    ];
    $fields['billing']['billing_longitude'] = [
        'label'     => __('Longitude (Billing)', 'woocommerce'),
        'required'  => false,
        'class'     => ['form-row-last'],
        'clear'     => true,
    ];

  
    $fields['shipping']['shipping_latitude'] = [
        'label'     => __('Latitude (Shipping)', 'woocommerce'),
        'required'  => false,
        'class'     => ['form-row-first'],
        'clear'     => true,
    ];
    $fields['shipping']['shipping_longitude'] = [
        'label'     => __('Longitude (Shipping)', 'woocommerce'),
        'required'  => false,
        'class'     => ['form-row-last'],
        'clear'     => true,
    ];

    return $fields;
}
add_filter('woocommerce_shipping_package_name', function($name) {
    return $name . '_' . sanitize_text_field($_POST['shipping_latitude'] ?? '') . '_' . sanitize_text_field($_POST['shipping_longitude'] ?? '');
});

add_filter('woocommerce_cart_shipping_packages', 'custom_add_coords_to_package');
function custom_add_coords_to_package($packages) {
    $customer_id = get_current_user_id();
    $slat = get_user_meta($customer_id, 'shipping_latitude', true);
    $slong = get_user_meta($customer_id, 'shipping_longitude', true);

    foreach ($packages as &$package) {
        $package['destination']['custom_lat'] = $slat;
        $package['destination']['custom_lng'] = $slong;
    }

    return $packages;
}
