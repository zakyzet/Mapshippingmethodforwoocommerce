<?php

if (!defined('ABSPATH')) exit;

class WC_Bluebird_Shipping_Method extends WC_Shipping_Method {

    public function __construct() {
        $this->id = 'bluebird_shipping';
        $this->method_title = 'Bluebird Shipping';
        $this->method_description = 'Shipping per kilometer via marker & map';
        $this->enabled = 'yes';
        $this->title = 'Bluebird ';

        $this->init();
    }

    public function init() {
      
        $this->init_form_fields();
        $this->init_settings();
    }

    public function calculate_shipping($package = array()) {
     
        $origin_lat = get_option('origin_latitude');
        $origin_lng = get_option('origin_longutude');
         $customer_id = get_current_user_id();
     //   $slat = get_user_meta($customer_id, 'shipping_latitude', true);
     //   $slong = get_user_meta($customer_id, 'shipping_longitude', true);
        
      //  $slat = isset($_GET['lat']) ? sanitize_text_field($_GET['lat']) : get_user_meta($customer_id, 'shipping_latitude', true);
      //  $slong = isset($_GET['lang']) ? sanitize_text_field($_GET['lang']) : get_user_meta($customer_id, 'shipping_longitude', true);
        $slat = isset($_POST['shipping_latitude']) ? sanitize_text_field($_POST['shipping_latitude']) : get_user_meta($customer_id, 'shipping_latitude', true);
        $slong = isset($_POST['shipping_longitude']) ? sanitize_text_field($_POST['shipping_longitude']) : get_user_meta($customer_id, 'shipping_longitude', true);
      
        $destination_lat = isset($_POST['shipping_latitude']) ? floatval($_POST['shipping_latitude']) : $slat;
        $destination_lng = isset($_POST['shipping_longitude']) ? floatval($_POST['shipping_longitude']) : $slong;

       
        $distance = $this->haversine_distance($origin_lat, $origin_lng, $destination_lat, $destination_lng);
        $rounded_distance = ceil($distance);
        // Tarif per km
        $rate_per_km = get_option('bluebird_price');
        $shipping_cost = $rounded_distance * $rate_per_km;
        
      
        $this->add_rate(array(
            'id'    => $this->id,
            'label' => $this->title . " (" . round($distance, 1) . " km)",
            'cost'  => $shipping_cost,
        ));
       
    }

    private function haversine_distance($lat1, $lon1, $lat2, $lon2) {
        $earth_radius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earth_radius * $c;
    }
}
