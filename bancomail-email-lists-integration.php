<?php
/*
Plugin Name: Bancomail Email Lists Integration
Plugin URI: https://wordpress.org/plugins/bancomail-email-lists-integration/
Description: A plugin that integrate Bancomail's products into wp
Version: 1.1.4
Author: Bancomail (Neosoft Srl) 
Author URI: http://www.bancomail.com
License: GPL2
*/

/*
 * Copyright 2017 Neosoft s.r.l.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation;
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class BMWS_BancomailEmailListsIntegration {
    
    public $user_info=array();
    public $quey_status=FALSE;
    public $protocol='';
    public $cart_details=array();
    public $total_orders='';
    public $total_results;
    public $error_msg='';

    public function __construct() {
          $this->protocol=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
          $this->BMWS_start();
    }
    
    public function BMWS_start(){
        // Set the core file path
        define( 'BMWS_FILE_PATH', dirname( __FILE__ ) );
        // Define the path to the plugin folder
        define( 'BMWS_DIR_NAME',  basename( BMWS_FILE_PATH ) );
        define( 'BMWS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
        // Define the URL to the plugin folder
        define( 'BMWS_FOLDER',    dirname( BMWS_PLUGIN_BASENAME ) );
        define( 'BMWS_URL',       plugins_url( '', __FILE__ ) );
        //load resources
        add_action('admin_enqueue_scripts', array($this, 'BMWS_load_css'));
        add_action('admin_enqueue_scripts', array($this, 'BMWS_load_js'));
        add_action('admin_menu', array( $this, 'BMWS_webservice_admin_menu'));
        add_action('wp', array( $this, 'BMWS_elenchi_email' ));
        add_action('wp_enqueue_scripts', array($this, 'BMWS_load_css'));
        add_action('wp_enqueue_scripts', array($this, 'BMWS_load_js'));
        //setup admin translation
        add_action('after_setup_theme', array($this,'BMWS_admin_language_setup'));
        //backend management
        if(is_admin()){
            //carica i dati relativi al tab corrente
            if(isset($_GET['tab']) && method_exists($this,'BMWS_'.sanitize_text_field($_GET['tab']))){
                $method='BMWS_'.sanitize_text_field($_GET['tab']);
                $this->user_info=$this->$method();
            }
            else if(!isset($_GET['tab'])){
                $this->user_info=$this->BMWS_general_info();
            }
            //admin 'Order history' page
            if(isset($_GET['page']) && sanitize_text_field($_GET['page'])=='bancomail-email-lists-integration-ordini'){
                //update order status
                if(isset($_POST['update_order']) && strlen($_POST['order_id']) > 0  && intval($_POST['order_id'])){
                    $order_id=intval($_POST['order_id']);
                    $order_status=sanitize_text_field($_POST['bm_ws_order_status']);
                    $update_order=(!$order_id || strlen($order_status)==0)? FALSE : $this->BMWS_update_order_status($order_id, $order_status);
                }
                $page=isset($_GET['p']) ? intval($_GET['p']) : FALSE;
                $this->orders=!$page ? $this->BMWS_get_orders() : $this->BMWS_get_orders($page);
                $this->total_orders=$this->BMWS_get_total_orders();
                //get order details
                if(isset($_GET['order_id'])){
                    $orderid=intval($_GET['order_id']);
                    $this->cart_details=!$orderid ? FALSE : $this->BMWS_get_cart($orderid);
                }
                
            }
            //salva dati di configurazione web service e dati relativi al profilo del partner
            if(isset($_POST['save_user_options']) && method_exists($this, 'BMWS_'.sanitize_text_field($_POST['save_user_options']))){
            
                if($_POST['save_user_options']=='set_general_info'){
                    $options=array('bm_email'=>sanitize_email(sanitize_text_field($_POST['bm_email'])),
                        'bm_password'=>sanitize_text_field($_POST['bm_password']),
                        'bm_languages'=>sanitize_text_field(implode('|',$_POST['bm_languages'])),
                        'bm_default_language'=>sanitize_text_field($_POST['bm_default_language']),
                        'bm_user_iva'=>sanitize_text_field($_POST['bm_user_iva']),
                        'bm_user_tax'=>sanitize_text_field($_POST['bm_user_tax'])
                    );
                    
                    //test ws connection
                    require_once(plugin_dir_path( __FILE__ )."scripts/ws.php");
                    $ws=new BMWS_Bancomail_Ws($options['bm_email'], $options['bm_password']);
                    $ws_connection=$ws->BM_WS_test_connection();
                    if($ws_connection!='Connection OK'){
                     require_once(plugin_dir_path( __FILE__ ).'scripts/translate.php');
                     $lang_=get_site_option('bm_default_language')!='' ? get_site_option('bm_default_language') : '';
                     $translate=new bm_ws_translate($lang_,'it',plugin_dir_path( __FILE__ ));
                     $this->quey_status=FALSE;
                     $this->error_msg=$translate->_print($ws_connection);
                    }
                    else{
                     $this->quey_status=$this->BMWS_set_general_info($options);
                    }
                }
            
            }
            //salva i dati relativi alla pagina di frontend
            if(isset($_POST['submit_frontend_setting']) && sanitize_text_field($_POST['submit_frontend_setting'])=='frontend'){
                $this->BMWS_set_frontend_info(sanitize_text_field($_POST['bm_ws_set_frontend_lang']));
            }
        }
        else{//front-end
            $this->user_info=$this->BMWS_general_info();
        }
    }
    
    static public function BMWS_install() {
        //we call this method after the plugin was activated
        //create the front-end page and store the id in option table
        //create the order table if not exist, to store the purchase history
        //create the cart table if not exist, to store each item in the cart
        $post_id = wp_insert_post(array (
            'post_type' => 'page',
            'post_author' => get_current_user_id(),
            'post_title' => 'database',
            'post_content' => '',
            'post_name' => 'database',
            'post_status' => 'publish'  
        ));
        add_site_option('bm_ws_front_page_postid',$post_id);
        
        
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_bm_ws_order_history = $wpdb->prefix . "bm_ws_order_history";
        $table_bm_ws_cart = $wpdb->prefix . "bm_ws_cart";
        $sql = "CREATE TABLE $table_bm_ws_order_history (
        id int(11) unsigned NOT NULL AUTO_INCREMENT,
        client_identifier int(11) NOT NULL,
        bm_order_id int(11) NOT NULL,
        client_name varchar(60) NOT NULL,
        client_rsoc varchar(30) NOT NULL,
        client_city varchar(30) NOT NULL,
        client_province varchar(30) NOT NULL,
        client_nation varchar(30) NOT NULL,
        client_phone_number varchar(15) NOT NULL,
        client_email varchar(60) NOT NULL,
        data datetime NOT NULL,
        total_records mediumint(9) NOT NULL,
        bm_price_novat_nodiscount decimal(8,2) NOT NULL,
        bm_applied_discount varchar(10) NOT NULL,
        bm_price_novat decimal(8,2) NOT NULL,
        bm_price_vat float NOT NULL,
        client_price_novat decimal(8,2) NOT NULL,
        client_price_vat decimal(8,2) NOT NULL,
        my_iva varchar(3) NOT NULL,
        payment_method varchar(20) NOT NULL,
        status varchar(10) NOT NULL,
        partner_payment_url varchar(500) NOT NULL,
        is_test tinyint(1) NOT NULL,
        PRIMARY KEY (id),
        KEY client_identifier (client_identifier,bm_order_id),
        KEY is_test (is_test)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        
        
        $sql_cart = "CREATE TABLE $table_bm_ws_cart (
        id int(11) NOT NULL,
        client_identifier int(11) NOT NULL,
        cart_id int(11) NOT NULL,
        order_id int(11) NOT NULL,
        package_id mediumint(9) NOT NULL,
        totale_anagrafiche mediumint(9) NOT NULL,
        bm_price_novat decimal(8,2) NOT NULL,
        bm_discount varchar(5) NOT NULL,
        macrocategory varchar(100) NOT NULL,
        location varchar(200) NOT NULL,
        ricarico_partner varchar(5) NOT NULL,
        KEY client_identifier (client_identifier),
        KEY cart_id (cart_id)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        
        try{
            if($wpdb->get_var("SHOW TABLES LIKE '$table_bm_ws_order_history'") !=  $table_bm_ws_order_history) {
                 dbDelta($sql);
            }
        } catch (Exception $e) {
                echo $e->getMessage();
                exit();
        }
        if($wpdb->get_var("SHOW TABLES LIKE '$table_bm_ws_cart'") !=  $table_bm_ws_cart) {
            dbDelta($sql_cart);
        }
    }
    
    static public function BMWS_uninstall() {
        //we call this method before uninstal the plugin
        //delete all plugin options
        $page_id=get_site_option('bm_ws_front_page_postid');
        wp_delete_post($page_id, true);
        $options=array('bm_email', 'bm_password', 'bm_languages', 'bm_default_language', 'bm_user_tax','bm_ws_front_page_postid', 'bm_user_iva','bm_paypal_standard','bm_bonifico','bm_frontend_it', 'bm_frontend_en', 'bm_frontend_fr', 'bm_frontend_es', 'bm_frontend_de', 'bm_checkout_it','bm_checkout_en','bm_checkout_fr','bm_checkout_de','bm_checkout_es','bm_data_treatment_it','bm_data_treatment_en','bm_data_treatment_es','bm_data_treatment_fr');
        foreach($options as $value){
           delete_site_option($value);
        }
        
    }
    
    public function BMWS_admin_language_setup() {
       load_plugin_textdomain('bancomail-email-lists-integration', false, dirname(plugin_basename(__FILE__)) . '/admin/lang/');
    }
    
    
    
    public function BMWS_load_js(){
       if(!is_admin()){ 
        wp_register_script('bm_front_end', plugins_url('js/bm_front.js', __FILE__),array("jquery"));
        wp_enqueue_script('bm_front_end');
       }
       else{
           wp_register_script('bm_admin_js', plugins_url('js/bm_admin_js.js', __FILE__),array("jquery"));
           wp_enqueue_script('bm_admin_js');
       }
    }
    
    
    public function BMWS_load_css(){
        if(!is_admin()){ 
         wp_register_style('bm_front_end_css', plugins_url('css/bm_style.css', __FILE__));
         wp_enqueue_style('bm_front_end_css');
        }
        else{
            wp_register_style('bm_admin_css', plugins_url('css/bm_admin_style.css', __FILE__));
            wp_enqueue_style('bm_admin_css');
        }
    }

    public function BMWS_webservice_admin_menu() {
        // Add a new submenu under Settings:
    	//add_options_page( 'Bancomail Web Service Plugin Options', 'BM Web Service', 'manage_options', 'bm-unique-identifier', 'bm_webservice_options' );
    	// Add a new submenu under Tools:
    	//add_management_page( __('BM Web Service Tools','menu-bm'), __('BM Web Service Tools','menu-bm'), 'manage_options', 'bmwebservicetools', 'bm_tools_page');
    	
    	// Add a new top-level menu (ill-advised): 
    	add_menu_page(__('Bancomail Email Lists Integration','menu-bm'), __('Bancomail Email Lists Integration','menu-bm'), 'manage_options', 'bancomail-email-lists-integration', array( $this, 'BMWS_webservice_admin_page' ));
    	// Add a submenu to the custom top-level menu:
    	add_submenu_page('bancomail-email-lists-integration', __(__('Ordini','bancomail-email-lists-integration'),'menu-bm'), __(__('Ordini','bancomail-email-lists-integration'),'menu-bm'), 'manage_options', 'bancomail-email-lists-integration-ordini', array( $this, 'BMWS_webservice_ordini'));
    }
    
    
    public function BMWS_webservice_admin_page() {
        require_once(BMWS_FILE_PATH . '/admin/bm_webservice_admin_page.php');
    }
    
    public function BMWS_webservice_ordini() {
        require_once(BMWS_FILE_PATH . '/admin/bm_webservice_ordini.php');
    }
    
    private function BMWS_general_info(){
          $options=array('bm_email'=>'', 'bm_password'=>'', 'bm_languages'=>'', 'bm_default_language'=>'', 'bm_user_tax'=>'', 'bm_user_iva'=> '','bm_ws_front_page_postid'=>'', 'bm_paypal_standard'=>'','bm_bonifico'=>'');
          foreach($options as $key => $value){
              $option_value=get_site_option($key,false,true);
              if($option_value!=''){
                  $options[$key]=$option_value;
              }
          }
          return $options;
    }
    
    
    private function BMWS_gateway(){
        //salva i dati relativi alle modalità di pagamento
        if(isset($_POST['submit_payments_setting']) && (sanitize_text_field($_POST['submit_payments_setting'])=='paypal_standard' || sanitize_text_field($_POST['submit_payments_setting'])=='bonifico')) {
            
           //paypal_standard
           if($_POST['submit_payments_setting']=='paypal_standard'){
                
                $paypal_standard=array();
                $paypal_standard['bm_paypal_standard_option']=(isset($_POST['bm_paypal_standard_option']) && sanitize_text_field($_POST['bm_paypal_standard_option'])=='active') ? 1 : 0;
                $paypal_standard['bm_paypal_standard_user_defined_name']=sanitize_text_field($_POST['bm_paypal_standard_user_defined_name']);
                $paypal_standard['bm_paypal_standard_username']=sanitize_text_field($_POST['bm_paypal_standard_username']);
                $paypal_standard['bm_paypal_standard_url']=sanitize_text_field($_POST['bm_paypal_standard_url']);
                $paypal_standard['bm_paypal_standard_ipn']=1;
                $paypal_standard['address_override']=0;
                if(get_site_option('bm_paypal_standard',false,true)!=''){
                    update_site_option('bm_paypal_standard',json_encode($paypal_standard));
                }
                else{
                    add_site_option('bm_paypal_standard',json_encode($paypal_standard));
                }
                $this->quey_status=TRUE;
            }
            
            
            //bonifico
            if($_POST['submit_payments_setting']=='bonifico'){  
                $paypal_standard=array();
                $language=strlen($_POST['bm_bonifico_instructions_language']) > 0 ? sanitize_text_field($_POST['bm_bonifico_instructions_language']) : '';
                if($language==''){
                    $this->quey_status=FALSE;
                    return false;
                }
                $bonifico['bm_bonifico_option']=(isset($_POST['bm_bonifico_option']) && sanitize_text_field($_POST['bm_bonifico_option'])=='bonifico') ? 1 : 0;
                $bonifico['bm_bonifico_user_defined_name']=sanitize_text_field($_POST['bm_bonifico_user_defined_name']);
               
                if(get_site_option('bm_bonifico',false,true)!=''){
                    $site_option=json_decode(get_site_option('bm_bonifico'));
                    $gateway=$site_option->bm_bonifico_instructions;
                    $bon['it']=isset($gateway->it) ? $gateway->it : '';
                    $bon['en']=isset($gateway->en) ? $gateway->en : '';
                    $bon['fr']=isset($gateway->fr) ? $gateway->fr : '';
                    $bon['es']=isset($gateway->es) ? $gateway->es : '';
                    $bon['de']=isset($gateway->de) ? $gateway->de : '';
                    //aggiorna instruzioni
                    $bon[$language]=strlen($_POST['bm_bonifico_instructions']) > 0 ? esc_html($_POST['bm_bonifico_instructions']) : '';
                    $bonifico['bm_bonifico_instructions']=$bon;   
                    update_site_option('bm_bonifico',json_encode($bonifico));
                }
                else{
                    $bonifico['bm_bonifico_instructions'][$language]=strlen($_POST['bm_bonifico_instructions']) > 0 ? esc_html($_POST['bm_bonifico_instructions']) : '';
                    add_site_option('bm_bonifico',json_encode($bonifico));
                }
                
            }
            $this->quey_status=TRUE;
        }
        
        $options=array('bm_paypal_standard'=>'', 'bm_paypal_express'=>'', 'bm_paypal_pro'=>'', 'bm_bonifico'=>'');
        foreach($options as $key => $value){
            $option_value=get_site_option($key,false,true);
            if($option_value!=''){
                $options[$key]=json_decode($option_value);
            }
        }
        
        $this->user_info=$options;
        return $options;
    }
    
    private function BMWS_set_general_info($options){
      
        //control if all values are ok
        foreach($options as $key => $value){
            if($value==''){
                return FALSE;
            }
            if($key=='bm_languages'){
                $languages=explode('|',$value);
                if(!in_array($options['bm_default_language'],$languages)){
                    return FALSE;
                }
            }
        }
       
        //update if is not the first time when we save this data
        if($this->user_info['bm_email']!=''){
            foreach($options as $key => $value){
                   update_site_option($key,$value);
            }
        }
        else{//insert if is the first time when we save
            foreach($options as $key => $value){
             add_site_option($key,$value);
            }
        }
        $this->user_info=$options;
        return TRUE;
    }
    
    
    private function BMWS_set_frontend_info($lang){
        if(strlen($lang)==2){
            $default_lang=$lang;
            $index='bm_frontend_'. $default_lang;
            $checkout=array();
            $checkout['bm_frontend_header']=strlen($_POST['bm_frontend_header_'.$default_lang]) > 0  ? esc_html($_POST['bm_frontend_header_'.$default_lang]) : '';
            $checkout['bm_frontend_footer']=strlen($_POST['bm_frontend_footer_'.$default_lang]) > 0 ? esc_html($_POST['bm_frontend_footer_'.$default_lang]) : '';
            if(get_site_option($index,false,true)!=''){
                update_site_option($index,json_encode($checkout));
            }
            else{
                add_site_option($index,json_encode($checkout));
            }
            $this->quey_status=TRUE;
        }
        else{
            $this->quey_status=FALSE;
        }
    }
    
    private function BMWS_checkout(){
        $default_lang=isset($_GET['lang']) ? urlencode(sanitize_text_field($_GET['lang'])) : get_site_option('bm_default_language',false,true);
        $index='bm_checkout_'. $default_lang;
        
        //salava i dati del partner relativi alla pagina checkout nel frontend
        if(isset($_POST['submit_checkout_setting']) && sanitize_text_field($_POST['submit_checkout_setting'])=='checkout'){
            $default_lang=(strlen($_POST['bm_ws_set_checkout_lang'])==2) ? sanitize_text_field($_POST['bm_ws_set_checkout_lang']) : '';
            if($default_lang==''){
                $this->quey_status=FALSE;
                return false;
            }
            $index='bm_checkout_'. $default_lang;
            $checkout=array();
            $checkout['bm_checkout_header']=strlen($_POST['bm_checkout_header_'.$default_lang]) > 0 ? esc_html($_POST['bm_checkout_header_'.$default_lang]) : '';
            $checkout['bm_checkout_footer']=strlen($_POST['bm_checkout_footer_'.$default_lang]) > 0 ? esc_html($_POST['bm_checkout_footer_'.$default_lang]) : '';
            $checkout['bm_checkout_privacy']=strlen($_POST['bm_checkout_privacy_'.$default_lang]) > 0 ? esc_html($_POST['bm_checkout_privacy_'.$default_lang]) : '';
            $checkout['bm_data_treatment']=strlen($_POST['bm_data_treatment_'.$default_lang]) > 0 ? esc_html($_POST['bm_data_treatment_'.$default_lang]) : '';
            $checkout['bm_checkout_email_header']=strlen($_POST['bm_checkout_email_header_'.$default_lang]) > 0 ? esc_html($_POST['bm_checkout_email_header_'.$default_lang]) : '';
            $checkout['bm_checkout_email_footer']=strlen($_POST['bm_checkout_email_footer_'.$default_lang]) > 0 ? esc_html($_POST['bm_checkout_email_footer_'.$default_lang]) : '';

            if(get_site_option($index,false,true)!=''){
                update_site_option($index,json_encode($checkout));
            }
            else{
                add_site_option($index,json_encode($checkout));
            }
            $this->quey_status=TRUE;
        }
        
        if($default_lang==''){
            return false;
        }

        return json_decode(get_site_option($index,false,true));
    }
    
    
   private function BMWS_set_user_id(){
        if(!isset($_COOKIE['bm_ws_carrello'])) {
            setcookie('bm_ws_carrello', time(), time() + (86400 * 365), "/", null, false, true); // 86400 = 1 day
            $client_id=$_COOKIE['bm_ws_carrello'];
        }
        else{
            $client_id=$_COOKIE['bm_ws_carrello'];
        }
        return $client_id;
    }
    
    protected function BMWS_get_orders($page=''){
        global $wpdb;
        $page=($page=='' || $page==1) ? 0 : $page;
        $table = $wpdb->prefix . "bm_ws_order_history";
        $table_cart = $wpdb->prefix . "bm_ws_cart";
        
        /*Max Number of results to show*/
        $max = 20;
        /*Get the current page*/
        $p = (intval($page) > 0) ? intval($page) : 0;
        $offset = (($p-1) * $max);
        $offset=$offset < 0 ? 0 : $offset;
        $results = $wpdb->get_results('SELECT * FROM '.$table.' ORDER BY id DESC LIMIT '.$max.' OFFSET '.$offset.'', ARRAY_A);
        return $results;
    }
    
    protected function BMWS_get_total_orders(){
        global $wpdb;
        $table = $wpdb->prefix . "bm_ws_order_history";
        $result = $wpdb->get_results('SELECT COUNT(*) AS total_orders FROM '.$table.'', ARRAY_A);
        return $result;
    }
    
    protected function BMWS_get_cart($order_id){
        global $wpdb;
        $table_cart = $wpdb->prefix . "bm_ws_cart";
        $results = $wpdb->get_results('SELECT * FROM '.$table_cart.' WHERE id='.trim($order_id).'', ARRAY_A);
        return $results;
    }
    
    protected function BMWS_update_order_status($id, $status){
        global $wpdb;
        $id=intval($id);
        $table_name = $wpdb->prefix . 'bm_ws_order_history';
        try {
            $wpdb->update(
                $table_name,
                array(
                    'status' => $status
                ),
                array('id' => $id), null, null
             );
        }
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
            return FALSE;
        }
       
        return TRUE;
    }
    
    
    private function BMWS_save_order($fields){
        global $wpdb;
        $table_name = $wpdb->prefix . 'bm_ws_order_history';
        try {
            $wpdb->insert(
                $table_name,
                array(
                    'client_identifier' => $fields['client_identifier'],
                    'bm_order_id' => $fields['bm_order_id'],
                    'client_name' => $fields['client_name'],
                    'client_rsoc' => $fields['client_rsoc'],
                    'client_city' => $fields['client_city'],
                    'client_province' => $fields['client_province'],
                    'client_nation' => $fields['client_nation'],
                    'client_phone_number' => $fields['client_phone_number'],
                    'client_email' => $fields['client_email'],
                    'data' => $fields['update_date'],
                    'total_records' => $fields['total_records'],
                    'bm_price_novat_nodiscount' => $fields['bm_price_novat_nodiscount'],
                    'bm_applied_discount' => $fields['bm_applied_discount'],
                    'bm_price_novat' => $fields['bm_price_novat'],
                    'bm_price_vat' => $fields['bm_price_vat'],
                    'client_price_novat' => $fields['client_price_novat'],
                    'my_iva' => $fields['my_iva'],
                    'client_price_vat' => $fields['client_price_vat'],
                    'payment_method' => $fields['payment_method'],
                    'status' => $fields['status'],
                    'partner_payment_url' => $fields['partner_payment_url'],
                    'is_test' => $fields['is_test']
                )
            );
            $get_last_id=$wpdb->insert_id;
            
        }
        catch (Exception $e) {
            return FALSE;
        }
        return  $get_last_id; 
    }
    
    private function BMWS_save_cart($id, $cart, $client_id, $order_id, $user_tax, $lang){
        global $wpdb;
        $table_name = $wpdb->prefix . 'bm_ws_cart';
        $naz_name='name_'.$lang;
        $macro_name=$lang.'_name';
        if(is_array($cart->YourCart->cli_package)){
            for($i=0;$i<sizeof($cart->YourCart->cli_package); $i++) {
                $location=$cart->YourCart->cli_package[$i]->nation->$naz_name;
                if(isset($cart->YourCart->cli_package[$i]->region->name)){
                    $location.='-'.$cart->YourCart->cli_package[$i]->region->name;
                }
                try {
                    $wpdb->insert(
                        $table_name,
                        array(
                            'id' => $id,
                            'client_identifier' => $client_id,
                            'cart_id' => $cart->YourCart->cart_id,
                            'order_id' => $order_id,
                            'package_id' => $cart->YourCart->cli_package[$i]->package_id,
                            'totale_anagrafiche' => $cart->YourCart->cli_package[$i]->tot_anag,
                            'bm_price_novat' => $cart->YourCart->cli_package[$i]->price_no_iva,
                            'bm_discount' => $cart->YourCart->cli_package[$i]->discount,
                            'macrocategory' => $cart->YourCart->cli_package[$i]->macrocategory->$macro_name,
                            'location' => $location,
                            'ricarico_partner' => $user_tax
                        )
                     );
                    }
                catch (Exception $e) {
                    echo 'Caught exception: ',  $e->getMessage(), "\n";
                    exit();
                    return FALSE;
                }
            }
          
            return TRUE;
        }
        else{
            $location=$cart->YourCart->cli_package->nation->$naz_name;
            if(isset($cart->YourCart->cli_package->region->name)){
                $location.='-'.$cart->YourCart->cli_package->region->name;
            }
            try {
                $wpdb->insert(
                    $table_name,
                    array(
                        'id' => $id,
                        'client_identifier' => $client_id,
                        'cart_id' => $cart->YourCart->cart_id,
                        'order_id' => $order_id,
                        'package_id' => $cart->YourCart->cli_package->package_id,
                        'totale_anagrafiche' => $cart->YourCart->cli_package->tot_anag,
                        'bm_price_novat' => $cart->YourCart->cli_package->price_no_iva,
                        'bm_discount' => $cart->YourCart->cli_package->discount,
                        'macrocategory' => $cart->YourCart->cli_package->macrocategory->$macro_name,
                        'location' => $location,
                        'ricarico_partner' => $user_tax
                    )
                 );
            }
            catch (Exception $e) {
                 echo 'Caught exception: ',  $e->getMessage(), "\n";
                 return FALSE;
            }
            return TRUE;
        }
        
    }
    
    
    private function BMWS_send_email_to_user($lang, $cart, $iva, $user_tax, $orderDetails){
        
      
        require_once(plugin_dir_path( __FILE__ ).'scripts/translate.php');
        $translate=new bm_ws_translate($lang,'it',plugin_dir_path( __FILE__ ));
        $naz_name='name_'.$lang;
        $item_name=$lang.'_name';
        $bm_checkout='bm_checkout_'.$lang;
        if(locate_template('bm-emaillist-integration/email.phtml')!=''){
            $email_template=file_get_contents(get_template_directory()."/bm-emaillist-integration/email.phtml", false, null);
        }
        else{
         $email_template=file_get_contents(plugin_dir_path( __FILE__ )."templates/email.phtml");
        
        }
       
        $email_info=json_decode(get_site_option($bm_checkout,false,true));
        $email_template=str_replace('[BLOG_URL]', get_option( 'siteurl' ),  $email_template);
        $email_template=str_replace('[BLOG_NAME]', get_bloginfo('name'),  $email_template);
        $email_template=str_replace('[EMAIL_HEADER]',  html_entity_decode($email_info->bm_checkout_email_header),  $email_template);
        $email_template=str_replace('[EMAIL_FOOTER]',  html_entity_decode($email_info->bm_checkout_email_footer),  $email_template);
        $email_template=str_replace('[CLIENT_NAME]', $orderDetails['client_name'],  $email_template);
        $email_template=str_replace('[CLIENT_EMAIL]', $orderDetails['client_email'],  $email_template);
        $email_template=str_replace('[CLIENT_PHONE]', $orderDetails['client_phone_number'],  $email_template);
        $email_template=str_replace('[CLIENT_COMPANY]', $orderDetails['client_rsoc'],  $email_template);
        $email_template=str_replace('[CLIENT_LOCATION]', $orderDetails['client_city'].'-'.$orderDetails['client_province'].'-'.$orderDetails['client_nation'],  $email_template);
        $html='<table cellspacing="1" cellspacing="1" width="100%">';
        $html.='<tr style="background:#5d5d5c;color:#ffffff;"><td width="100px;">Id</td><td>'.$translate->_print('Target Database').'</td>';
        $html.='<td>'.$translate->_print('Anagrafiche').'</td><td>'.$translate->_print('Prezzo').'<br/>('.$translate->_print('iva escl.').')</td></tr>';
        $item_total_noiva=0;
        if(is_array($cart->YourCart->cli_package)){
            for($i=0;$i<sizeof($cart->YourCart->cli_package); $i++) {
                $location=$cart->YourCart->cli_package[$i]->macrocategory->$item_name.'-'.$cart->YourCart->cli_package[$i]->nation->$naz_name;
                if(isset($cart->YourCart->cli_package[$i]->region->name)){
                    $location.='-'.$cart->YourCart->cli_package[$i]->region->name;
                }
                $html.='<tr>';
                $html.='<td style="background:#f1f1f1;" width="100">'.$cart->YourCart->cli_package[$i]->package_id.'</td>';
                $html.='<td style="background:#f1f1f1;">'.$location.'</td>';
                $html.='<td style="background:#f1f1f1;">'.$cart->YourCart->cli_package[$i]->tot_anag.'</td>';
                if($cart->YourCart->cli_package[$i]->discount > 0){
                    $discounted_item_price=($cart->YourCart->cli_package[$i]->price_no_iva - ($cart->YourCart->cli_package[$i]->price_no_iva * $cart->YourCart->cli_package[$i]->discount) /100);
                    $item_price = ($discounted_item_price + ($discounted_item_price * $user_tax) /100);
                }
                else{
                    $item_price = ($cart->YourCart->cli_package[$i]->price_no_iva + ($cart->YourCart->cli_package[$i]->price_no_iva * $user_tax) /100);
                }
                
               
                $html.='<td style="background:#f1f1f1;">'.number_format($item_price,2,',','.').'&euro;</td>';
                $html.='</tr>';
                $item_total_noiva+=$item_price;
            }
            
            $total_iva=($item_total_noiva + ($item_total_noiva * $iva) /100);
            $html.='<tr><td colspan="4" height="20"></td></tr>';
            $html.='<tr><td colspan="4">';
            $html.='<p>'.$translate->_print('Metodo di pagamento').':<br/>'.ucfirst(strtolower($translate->_print($orderDetails['payment_method']))).'</p>';
            $html.='<p style="text-align:right;"><span style="float:left;">'.$translate->_print('Totale IVA escl.').':</span>'.number_format($item_total_noiva,2,',','.').' &euro;</p>';
            //$html.=$translate->_print('iva').': '.$iva.'%<br/>';
            $html.='<p style="text-align:right;"><span style="float:left;">'.$translate->_print('Totale IVA').' ('.$iva.'%).'.$translate->_print('incl.').':</span> '.number_format($total_iva,2,',','.').' &euro;</p></td></tr>';
        }
        else{
            
            $location=$cart->YourCart->cli_package->macrocategory->$item_name.'-'.$cart->YourCart->cli_package->nation->$naz_name;
            if(isset($cart->YourCart->cli_package->region->name)){
                $location.='-'.$cart->YourCart->cli_package->region->name;
            }
            $html.='<tr>';
            $html.='<td style="background:#f1f1f1;" width="100">'.$cart->YourCart->cli_package->package_id.'</td>';
            $html.='<td style="background:#f1f1f1;">'.$location.'</td>';
            $html.='<td style="background:#f1f1f1;">'.$cart->YourCart->cli_package->tot_anag.'</td>';
            if($cart->YourCart->cli_package->discount > 0){
                $discounted_item_price=($cart->YourCart->cli_package->price_no_iva - ($cart->YourCart->cli_package->price_no_iva * $cart->YourCart->cli_package->discount) /100);
                $item_price = ($discounted_item_price + ($discounted_item_price * $user_tax) /100);
            }
            else{
                $item_price = ($cart->YourCart->cli_package->price_no_iva + ($cart->YourCart->cli_package->price_no_iva * $user_tax) /100);
            }
            
             
            $html.='<td style="background:#f1f1f1;">'.number_format($item_price,2,',','.').'&euro;</td>';
            $html.='</tr>';
            $item_total_noiva+=$item_price;
          
            $total_iva=($item_total_noiva + ($item_total_noiva * $iva) /100);
            $html.='<tr><td colspan="4" height="20"></td></tr>';
            $html.='<tr><td colspan="4">';
            $html.='<p>'.$translate->_print('Metodo di pagamento').':<br/>'.$translate->_print($orderDetails['payment_method']).'</p>';
            $html.='<p style="text-align:right;"><span style="float:left;">'.$translate->_print('Totale(iva escl.)').':</span>'.number_format($item_total_noiva,2,',','.').' &euro;</p>';
            //$html.=$translate->_print('iva').': '.$iva.'%<br/>';
            $html.='<p style="text-align:right;"><span style="float:left;">'.$translate->_print('Totale IVA').' ('.$iva.'%).'.$translate->_print('incl.').':</span> '.number_format($total_iva,2,',','.').' &euro;</p></td></tr>';
            
        }
        
        $html.='</table>';
        
        $bonifico=json_decode($this->user_info['bm_bonifico']);
        if($orderDetails['payment_method']=='bonifico'){
          $email_template=str_replace('[BONIFICO]', html_entity_decode($bonifico->bm_bonifico_instructions->$lang), $email_template);
        }
        else{
            $email_template=str_replace('[BONIFICO]','', $email_template);
        }
        $email_template=str_replace('[CARRELLO]', $html,  $email_template);
        $email_template=str_replace('[Riepilogo_acquisto]', $translate->_print('Riepilogo acquisto'),  $email_template);
        $from=get_option('admin_email');
        $to = $orderDetails['client_email'];
        $subject = $translate->_print('Il tuo ordine: #').$orderDetails['bm_order_id'].' '.$translate->_print('su').' '.get_bloginfo('name');
        $body = $email_template;
        $headers = array('Content-Type: text/html; charset=UTF-8','From: '.get_bloginfo('name').' <'.$from.'>');
        try {
         $email = wp_mail( $to, $subject, $body,  $headers); 
         $headers = array('Content-Type: text/html; charset=UTF-8','From: '.get_bloginfo('name').' <'.$to.'>');
         $email_to_admin=wp_mail( $from, $subject, $body, $headers);
         return $email;
        }
        catch (Exception $e){
            return $e->getMessage();
        }
    }
    
    public function BMWS_elenchi_email(){
        if(is_page('database')){
            //se il web service non è stato ancora configurato
            if($this->user_info['bm_email']==''&& $this->user_info['bm_password']==''){
                wp_redirect(home_url());
                exit;
            }
            
            //set client identifier
            $client_id=$this->BMWS_set_user_id();
            $dir = plugin_dir_path( __FILE__ );
            require_once($dir."scripts/ws.php");
    
            $ws=new BMWS_Bancomail_Ws($this->user_info['bm_email'], $this->user_info['bm_password']);
            $ws_connection=$ws->BM_WS_test_connection();
            
            //se la connessione al web service è falitta
            if($ws_connection!='Connection OK'){
                $addToCartResponse=array('msg'=>$ws_connection,'status'=>'ko');
            }
    
            //carica la pagina ricerca elenchi page
            if(!isset($_POST['checkout'])){
                $lang=(isset($_GET['lang']) && strlen($_GET['lang'])==2) ? sanitize_text_field($_GET['lang']) : $this->user_info['bm_default_language'];
                require_once(plugin_dir_path( __FILE__ ).'scripts/translate.php');
                $translate=new bm_ws_translate($lang,'it',plugin_dir_path( __FILE__ ));
                
                $parameters['lang']=$lang;
                if(isset($_GET['macro']) && $_GET['macro']!='' && strlen($_GET['macro']) < 4 && intval($_GET['macro']) > 0 && intval($_GET['macro']) < 298){
                    $parameters['macrocategory']=intval($_GET['macro']);
                }
                if(isset($_GET['naz']) && strlen($_GET['naz'])==2){
                    $parameters['nation']=sanitize_text_field($_GET['naz']);
                }
                if(isset($_GET['region']) && $_GET['region']!='' && intval($_GET['region']) > 0 && intval($_GET['region']) < 3893){
                    $parameters['region']=intval($_GET['region']);
                }
                $getNations=!isset($parameters['macrocategory']) ? $ws->BM_WS_get_all_nations($lang) : $ws->BM_WS_get_nation_by_category($lang, $parameters['macrocategory']);
                $getCategories=!isset($parameters['nation']) ? $ws->BM_WS_get_categories($lang) : $ws->BM_WS_get_catgories_by_nation($lang,$parameters['nation']);
                if(isset($parameters['nation']) && isset($parameters['macrocategory'])){
                    $getRegions=$ws->BM_WS_get_region_by_category_and_nation($parameters['nation'],$parameters['macrocategory']);
                }
                else if(isset($parameters['nation']) && !isset($parameters['macrocategory'])){
                    $getRegions=$ws->BM_WS_get_all_regions($parameters['nation']);
                }
    
                $getEmailLists=(!isset($parameters['nation']) && !isset($parameters['macrocategory'])) ? $ws->BM_WS_get_promo_lists($lang) : $ws->BM_WS_get_pricelist_selection($parameters);
                $this->total_results=(!isset($parameters['nation']) && !isset($parameters['macrocategory'])) ? count($getEmailLists->package) : count($getEmailLists);
               
                //cart management
    
                //remove from cart
                if(isset($_POST['bm_remove_item']) && strlen($_POST['bm_remove_item']) > 0 && intval($_POST['bm_remove_item'])){
                    $remove_item=$ws->BM_WS_removeItem(intval($_POST['bm_remove_item']),$client_id);
                    if($remove_item->result==true){
                        $addToCartResponse=array('msg'=>$translate->_print('Il prodotto &egrave; stato rimosso dal carrello con successo!'),'status'=>'ok');
                    }
                    else{
                        $addToCartResponse=array('msg'=>$translate->_print('Si &egrave; verificato un errore riprova pi&ugrave; tardi!'),'status'=>'ko');
                    }
                }
                //add to cart
                if(isset($_POST['bm_product']) && strlen($_POST['bm_product']) > 0 && intval($_POST['bm_product'])){
                    $addToCart=$ws->BM_WS_addPackage(filter_var($_POST['bm_product'], FILTER_SANITIZE_STRING), $client_id);
                    if($addToCart->result==true){
                        $addToCartResponse=array('msg'=>$translate->_print('Il prodotto &egrave; stato aggiunto al carrello con successo!'),'status'=>'ok');
                    }
                    else{
                        $msg=$addToCart->message=='Package is already in cart' ? $translate->_print('Il pacchetto &egrave; gi&agrave; nell carrello!') : $translate->_print('Si &egrave; verificato un errore riprova pi&ugrave; tardi!');
                        $addToCartResponse=array('msg'=>$msg,'status'=>'ko');
                    }
                }
                
                //get cart
                $cart_exists=$ws->BM_WS_get_cart_at_glance($client_id);
                if($cart_exists->CartSummary->total_packages > 0){
                    $getCart=$ws->BM_WS_check_out_cart($lang, $client_id);
                     
                }
                
                //paypal response
                if(isset($_GET['paypal']) && isset($_GET['order_id']) && intval($_GET['order_id']) && sanitize_text_field($_GET['paypal'])=='canceled'){
				    $update=$this->BMWS_update_order_status(intval($_GET['order_id']), 'ANNULLATO');
					if(isset($_GET['page_id']) && intval($_GET['page_id'])){
                        $post = get_post();
                        $location='/?page_id='.$post->ID;
                    }
                    else{
                        $location='/database/';
                    }
                    header('location:'.get_site_url().$location);
                    exit();
                }
                else if(isset($_GET['paypal']) && sanitize_text_field($_GET['paypal'])=='ok' && isset($_GET['order_id']) && intval($_GET['order_id'])){
                    $update=$this->BMWS_update_order_status(intval($_GET['order_id']), 'PAGATO');
					if(isset($_GET['page_id']) && intval($_GET['page_id'])){
                        $post = get_post();
                        $location='/?page_id='.$post->ID.'&paypal=';
                    }
                    else{
                        $location='/database/?paypal=';
                    }
                    header('location:'.get_site_url().$location.'ok&lang='.urlencode(sanitize_text_field($_GET['lang'])));
                }
                else if(isset($_GET['paypal']) && sanitize_text_field($_GET['paypal'])=='ok' && !isset($_GET['order_id'])){
                    $addToCartResponse=array('msg'=>$translate->_print('Grazie per il tuo acquisto! Controlla la tua Email, ti abbiamo inviato un’email di conferma.'),'status'=>'ok');
                }
    
    
                include($dir."templates/elenchi-email.php");
            }
            //checkout page
            elseif(isset($_POST['checkout']) && strlen($_POST['checkout']) > 0 && intval($_POST['checkout'])){
                
                $lang=(isset($_POST['lang']) && strlen($_POST['lang'])==2) ? sanitize_text_field($_POST['lang']) : $this->user_info['bm_default_language'];
               
                require_once(plugin_dir_path( __FILE__ ).'scripts/translate.php');
                $translate=new bm_ws_translate($lang,'it',plugin_dir_path( __FILE__ ));
                //controllo se client id è coretto
                if(!preg_match('/[|,\',\/,\\,",-,+,*,#,@,`,~,!,?,>,<,{,},\[,\],=,(,),&,$,%,^]/',$_COOKIE['bm_ws_carrello']) && strlen($_COOKIE['bm_ws_carrello']) >= 10 && strlen($_COOKIE['bm_ws_carrello']) <= 150){
                    $client_id=$_COOKIE['bm_ws_carrello'];
                }
                else{
                 include($dir."templates/elenchi-email.php");
                 exit();
                }
                //get cart
                $cart_exists=$ws->BM_WS_get_cart_at_glance($client_id);
                if($cart_exists->CartSummary->total_packages > 0){
                    $getCart=$ws->BM_WS_check_out_cart($lang, $client_id);
                }
                //user submit the form
                //if  $_POST['_name']!='' it is not a human that submited the form
                if(isset($_POST['acquista']) && intval($_POST['acquista'])==intval($client_id) && $_POST['_name']==''){
                    $client_name=sanitize_text_field($_POST['bm_ws_client_name']);
                    $rsoc=sanitize_text_field($_POST['bm_ws_client_rsoc']);
                    $city=sanitize_text_field($_POST['bm_ws_client_city']);
                    $province=$_POST['bm_ws_client_province']!='' ? sanitize_text_field($_POST['bm_ws_client_province']) : 'NO PROVINCE';
                    $nation=sanitize_text_field($_POST['bm_ws_client_nation']);
                    $phone=sanitize_text_field($_POST['bm_ws_client_phone_number']);
                    $email=sanitize_email(sanitize_text_field($_POST['bm_ws_client_email']));
                    $repeat_email=sanitize_email(sanitize_text_field($_POST['bm_ws_client_repeat_email']));
    
                    if($email!='' && $email==$repeat_email && $rsoc!='' && $client_name!='' && $city!='' && $nation!=''){
                        //confirm order
                        $placeOrder=$ws->BM_WS_confirm_order($client_id, $rsoc, $city, $province, $nation, $email, $phone);
                       
                        if($placeOrder->result==true){
                            $order_id=explode(';',$placeOrder->message);
                            $order_id=explode('|',$order_id[0]);
                            $payment_url=explode(';',$placeOrder->message);
                            $payment_url=explode('|',$payment_url[4]);
                            $payment_url=$payment_url[1];
                            $payment_method=sanitize_text_field($_POST['bm_ws_client_payment_method'])=='paypal' ? 'NON PAGATO' : 'IN ATTESA';
                            $order_parameters=array(
                                'client_identifier'=>$client_id,
                                'bm_order_id'=>trim($order_id[1]),
                                'client_name'=>$client_name,
                                'client_rsoc'=>$rsoc,
                                'client_city'=>$city,
                                'client_province'=>$province,
                                'client_nation'=>$nation,
                                'client_phone_number'=>$phone,
                                'client_email'=>$email,
                                'update_date'=>date('Y-m-d H:i:s'),
                                'total_records'=>sanitize_text_field($_POST['total_records']),
                                'bm_price_novat_nodiscount'=>sanitize_text_field($_POST['bm_price_novat_nodiscount']),
                                'bm_applied_discount'=>sanitize_text_field($_POST['bm_applied_discount']),
                                'bm_price_novat'=>sanitize_text_field($_POST['bm_price_novat']),
                                'bm_price_vat'=>sanitize_text_field($_POST['bm_price_vat']),
                                'client_price_novat'=>sanitize_text_field($_POST['client_price_novat']),
                                'client_price_vat'=>sanitize_text_field($_POST['client_price_vat']),
                                'my_iva'=>$this->user_info['bm_user_iva'],
                                'payment_method'=>sanitize_text_field($_POST['bm_ws_client_payment_method']),
                                'status'=> $payment_method,
                                'partner_payment_url'=>$payment_url,
                                'is_test'=>strpos($this->user_info['bm_email'], 'test__')!==false ? 1 : 0
                            );
                            //save order
                            $save_order=$this->BMWS_save_order($order_parameters);
                            //save cart details
                            if($save_order!=FALSE){
                                $this->BMWS_save_cart($save_order, $getCart, $client_id, $order_id[1], $this->user_info['bm_user_tax'], $lang);
                                //send email order summary
                                $send_email=$this->BMWS_send_email_to_user($lang, $getCart, $this->user_info['bm_user_iva'], $this->user_info['bm_user_tax'], $order_parameters);
                            }
    
                            //go to paypal
                            if(sanitize_text_field($_POST['bm_ws_client_payment_method'])=='paypal' && $save_order!=FALSE){
                                $nation_cat='name_'.$lang;
                                $item_name=$lang.'_name';
                                $this->user_info['bm_paypal_standard']=json_decode($this->user_info['bm_paypal_standard']);
                                $html_form='';
                                if(is_array($getCart->YourCart->cli_package)){
                                    for($i=0;$i<sizeof($getCart->YourCart->cli_package); $i++) {
                                        if($getCart->YourCart->cli_package[$i]->discount > 0){
                                            $discounted_item_price=($getCart->YourCart->cli_package[$i]->price_no_iva - ($getCart->YourCart->cli_package[$i]->price_no_iva * $getCart->YourCart->cli_package[$i]->discount) /100);
                                            $item_price = ($discounted_item_price + ($discounted_item_price * $this->user_info['bm_user_tax']) /100);
                                        }
                                        else{
                                            $item_price = ($getCart->YourCart->cli_package[$i]->price_no_iva + ($getCart->YourCart->cli_package[$i]->price_no_iva * $this->user_info['bm_user_tax']) /100);
                                        }
                                        
                                        $iva=(($item_price * $this->user_info['bm_user_iva']) /100);
                                        //$item_price_vat=number_format(($item_price+$iva),2);
                                        $item_price_vat=round(($item_price+$iva),2);
                                        $item_index=($i+1);
                                        $item=$getCart->YourCart->cli_package[$i]->macrocategory->$item_name.' - '.$getCart->YourCart->cli_package[$i]->nation->$nation_cat;
                                        $html_form.='<input type="hidden" name="item_name_'.$item_index.'" value="'.$item.'"/>';
                                        $html_form.='<input type="hidden" name="amount_'.$item_index.'" value="'.$item_price_vat.'"/>';
                                        $html_form.='<input type="hidden" name="quantity_'.$item_index.'" value="1"/>';
                                    }
                 
                                }
                                else{
                                    
                                    if($getCart->YourCart->cli_package->discount > 0){
                                        $discounted_item_price=($getCart->YourCart->cli_package->price_no_iva - ($getCart->YourCart->cli_package->price_no_iva * $getCart->YourCart->cli_package->discount) /100);
                                        $item_price = ($discounted_item_price + ($discounted_item_price * $this->user_info['bm_user_tax']) /100);
                                        
                                    }
                                    else{
                                        $item_price = ($getCart->YourCart->cli_package->price_no_iva + ($getCart->YourCart->cli_package->price_no_iva * $this->user_info['bm_user_tax']) /100);
                                    }
                                    $iva=(($item_price * $this->user_info['bm_user_iva']) /100);
                                    //$item_price_vat=number_format(($item_price+$iva),2);
                                    $item_price_vat=round(($item_price+$iva),2);
                                    $item=$getCart->YourCart->cli_package->macrocategory->$item_name.' - '.$getCart->YourCart->cli_package->nation->$nation_cat;
                                    $html_form.='<input type="hidden" name="item_name_1" value="'.$item.'"/>';
                                    $html_form.='<input type="hidden" name="amount_1" value="'.$item_price_vat.'"/>';
                                    $html_form.='<input type="hidden" name="quantity_1" value="1"/>';
                                }
                                
                                get_header();
								if(isset($_GET['page_id']) && intval($_GET['page_id'])){
                                    $post = get_post();
                                    $returnUrl='?page_id='.$post->ID.'&paypal=';
                                }
                                else{
                                    $returnUrl='/database/?paypal=';
                                }
                                echo '<div class="bm_ws_preloader"><div class="bm_spinnerContainer"><div class="loader"></div></div></div>';
                                echo '<div class="bm_wrapper"><div class="bm_container alert_box alert_ok">';
                                echo '<form id="pay-now"  action='.$this->user_info['bm_paypal_standard']->bm_paypal_standard_url.' method="post">
                                       <input type="hidden" name="business" value="'.$this->user_info['bm_paypal_standard']->bm_paypal_standard_username.'" />
                                       <input type="hidden" name="first_name" value="'.sanitize_text_field($_POST['bm_ws_client_name']).'"/>
                                       <input type="hidden" name="payer_email" value="'.$email.'"/>
                                       <input type="hidden" name="cmd" value="_cart"/>
                                       <input type="hidden" name="upload" value="1"/>
                                       <input type="hidden" name="currency_code" value="EUR"/>
                                       <input type="hidden" name="return" value="'.stripslashes(get_site_url().$returnUrl.'ok&order_id='.$save_order).'&lang='.$lang.'"/>
                                       <input type="hidden" name="cancel_return" value="'.get_site_url().$returnUrl.'canceled&order_id='.$save_order.'&lang='.$lang.'"/>
                                       <input type="hidden" name="notify_url" value="'.get_site_url().$returnUrl.'notify"/>';
                                 
                                echo $html_form;
                                echo '</form>';
                                echo '<a href="javascript:" onClick="javascript:document.getElementById(\'pay-now\').submit();">'.$translate->_print('Se non vieni reindirizzato automaticamente entro 5 secondi clicca qui.').'</a>';
                                echo '</div></div>';
                                echo '<script type="text/javascript">document.getElementById("pay-now").submit();setTimeout(function(){document.querySelector(\'.bm_ws_preloader\').setAttribute(\'class\',\'bm_ws_preloader hidden\');},5000);</script>';
                                get_footer();
                                exit();
                            }
                            
                            $order_placed_ok=true;
                            //delete client_id
                            //setcookie("bm_ws_carrello", "", time() - 3600);
                        }
                        else{
                           
                            $addToCartResponse=array('msg'=>$translate->_print('Verifica se hai compilato correttamente tutti i campi!'),'status'=>'ko');
                        }
                    }
                    else{
                    
                        $addToCartResponse=array('msg'=> $translate->_print('Si &egrave; verificato un errore riprova pi&ugrave; tardi!'),'status'=>'ko');
                    }
                }
                
                
                include($dir."templates/cehckout.php");
            }
    
            exit();
        }
    }
    
    
}


register_activation_hook( __FILE__, array( 'BMWS_BancomailEmailListsIntegration', 'BMWS_install' ) );
register_deactivation_hook(__FILE__, array( 'BMWS_BancomailEmailListsIntegration', 'BMWS_uninstall' ) );
//instantiate the class
$wpec = new BMWS_BancomailEmailListsIntegration();
?>