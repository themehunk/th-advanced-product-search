<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'TH_Advance_Product_Search' ) ):

    class TH_Advance_Product_Search {
         /**
         * Member Variable
         *
         * @var object instance
         */

       
       private static $instance;
       private $_settings_api;
  
       /**
         * Initiator
         */
        public static function instance() {
            if ( ! isset( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Constructor
         */
        public function __construct(){
        $this->includes();
        $this->hooks();

        }

        public function includes() {
            if ( $this->is_required_php_version() && $this->is_wc_active() ) {
                require_once TH_ADVANCE_PRODUCT_SEARCH_PLUGIN_PATH . '/inc/thaps-settings.php';
                require_once TH_ADVANCE_PRODUCT_SEARCH_PLUGIN_PATH . '/inc/thaps-option-setting.php';
                require_once TH_ADVANCE_PRODUCT_SEARCH_PLUGIN_PATH . '/inc/thaps-hook.php';
                require_once TH_ADVANCE_PRODUCT_SEARCH_PLUGIN_PATH . '/inc/thaps-front-custom-style.php';
            }
        }

        public function hooks() {
            if($this->is_wc_active()){
                add_action( 'init', array( $this, 'settings_api' ), 5 );
                add_shortcode( 'th-aps', array( $this, 'register_shortcode' ), 5 );
                add_filter( 'body_class', array( $this, 'body_class' ) );
                add_action( 'wp_enqueue_scripts', array( $this, 'th_advance_product_search_scripts' ), 15 );

            }

        }

        public function is_wc_active() {
            return class_exists( 'WooCommerce' );
        }
        
        public function images_uri( $file ) {
            $file = ltrim( $file, '/' );

            return TH_VARIATION_SWATCHES_IMAGES_URI . $file;
        }

        public function is_required_php_version() {
            return version_compare( PHP_VERSION, '5.6.0', '>=' );
        }

        public function settings_api() {

            if ( ! $this->_settings_api &&  $this->is_wc_active() )  {
                $this->_settings_api = new TH_Advancde_Product_Search_Set();
            }

            return $this->_settings_api;
        }

        public function get_option( $id ) {
           
            if ( ! $this->_settings_api ) {
                $this->settings_api();
            }
            
            return $this->_settings_api->get_option( $id );
        }

        public function get_options() {
            return get_option( 'th_advance_product_search' );
        }

        public function body_class( $classes ) {
           
            $old_classes = $classes;
            if ( apply_filters( 'disable_thvs_body_class', false ) ) {
                return $classes;
            }
            array_push( $classes, 'th-advance-product-search' );
            if ( wp_is_mobile() ) {
                array_push( $classes, 'th-advance-product-search-on-mobile' );
            }
            
            return apply_filters( 'thads_body_class', array_unique( $classes ), $old_classes );
        }

        public function th_advance_product_search_scripts(){

          wp_enqueue_style( 'th-advance-product-search-front', TH_ADVANCE_PRODUCT_SEARCH_PLUGIN_URI. '/assets/css/thaps-front-style.css', array(), TH_ADVANCE_PRODUCT_SEARCH_VERSION );

          wp_enqueue_script( 'th-advance-product-search-front', TH_ADVANCE_PRODUCT_SEARCH_PLUGIN_URI. '/assets/js/thaps-front.js', array(
                    'jquery',
                    'wp-util',
                    'underscore',
                    'wc-add-to-cart-variation'
                ),true);
          wp_localize_script(
                'th-advance-product-search-front', 'th_advance_product_search_options', apply_filters(
                    'th_advance_product_search_js_options', array(
                        'is_product_page'           => is_product(),
                        'thvs_nonce'                => wp_create_nonce( 'th_advance_product_search' ),
                    )
                )
            );

        }

         public function add_setting( $tab_id, $tab_title, $tab_sections, $active = false, $is_pro_tab = false, $is_new = false ) {
            add_filter(
                'thaps_settings', function ( $fields ) use ( $tab_id, $tab_title, $tab_sections, $active, $is_pro_tab, $is_new ) {
                array_push(
                    $fields, array(
                        'id'       => $tab_id,
                        'title'    => esc_html( $tab_title ),
                        'active'   => $active,
                        'sections' => $tab_sections,
                        'is_pro'   => $is_pro_tab,
                        'is_new'   => $is_new
                    )
                );

                return $fields;
            }
            );
        }

       public function register_shortcode(){

         require_once TH_ADVANCE_PRODUCT_SEARCH_PLUGIN_PATH . '/inc/thaps-search-from.php';

       }
 
}
// Load Plugin
    function th_advance_product_search(){
        return TH_Advance_Product_Search::instance();
    }
    add_action( 'plugins_loaded', 'th_advance_product_search', 25 );
endif; 