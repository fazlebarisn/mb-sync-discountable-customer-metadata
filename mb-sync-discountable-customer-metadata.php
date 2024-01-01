<?php 
/*
 * Plugin Name:       MB Synchronize Discountable Customer Metadata
 * Description:       This plugin synchronizes all discountable customer metadata
 * Version:           0.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            CanSoft
 * Author URI:        https://cansoft.com/
 */
// Include your functions here

require_once( plugin_dir_path( __FILE__ ) . '/inc/all-functions/get-customer-id-by-customer_code-meta-value.php');
require_once( plugin_dir_path( __FILE__ ) . '/inc/all-functions/mb-customer-meta-sync.php');


 require_once( plugin_dir_path( __FILE__ ) . '/inc/api/fetch-all-customer-metadata-from-arcmm-table.php');


//WORDPRESS HOOK FOR ADD A CRON JOB EVERY 2 Min

function mb_customer_discountable_metadata_cron_schedules($schedules){
    if(!isset($schedules['every_twelve_hours'])){
        $schedules['every_twelve_hours'] = array(
            'interval' => 12*60*60, // Every 12 hours
            'display' => __('Every 12 hours'));
    }
    return $schedules;
}

add_filter('cron_schedules','mb_customer_discountable_metadata_cron_schedules');




// Enqueue all assets
function mbdcm_metadata_all_assets(){
    wp_enqueue_script('mbdcm-script', plugin_dir_url( __FILE__ ) . 'assets/admin/js/script.js', null, time(), true);
}
add_action( 'admin_enqueue_scripts', 'mbdcm_metadata_all_assets' );


/**
 * Add menu page for this plugin
 */
function mbdcm_sync_menu_pages(){
    //add_menu_page('', 'Customer Sync', 'manage_options', 'mb-customer-sync', 'customer_sync_page');

    add_submenu_page( 'mb_syncs', 'Disountable Customer Metadata', 'Disountable Customer Metadata', 'manage_options', 'mbdcm-sync', 'mbdcm_sync_page' );
}
add_action( 'admin_menu', 'mbdcm_sync_menu_pages' , 999 );

/**
 * Main function for product sync
 */
function mbdcm_sync_page(){
    ?>
    <style>
        .wrap .d-flex {
            display: flex;
            align-items: center;
            justify-content: space-evenly;
        }
    </style>
        <div class="wrap">
            <h1>Disountable Customer Metadata</h1><br>
            <p>This pluging use for synchronization all customer metadata who is eligible for discount.</p>
            <div class="d-flex">
            	<form method="GET">

	                <input type="hidden" name="page-no" value="1">
	                <input type="hidden" name="page" value="mbdcm-sync">

	                <?php submit_button('Save', 'primary', 'mb-customer-metadata-sync'); ?>

	            </form>

                <form method="POST">
                    <?php 
                        submit_button( 'Start arcmm Cron Now', 'primary', 'mb-arcmm-sync-cron' );
                        // submit_button( 'Menual Start', 'primary', 'mb-arcmm-menual-sync-cron' );
                    ?>
                </form>
            </div>
          
            <?php 

                if(isset($_GET['page-no'])){
                        
                    $pageno = $_GET['page-no'] ?? 1;
                    
                    $all_customer_meta = fetch_all_customer_metadat_form_arcmn_table($pageno);

                    //dd($all_customer_meta);
                    $api_ids = [];

                    // $start = microtime(true);
                    $arraychunk = array_chunk($all_customer_meta, 2);

                    foreach ($arraychunk as $all_metas) {
                   
                        foreach($all_metas as $_c_meta){
                            
                        	//dd($_c_meta);
                            $api_ids[] = $_c_meta['id'];

                       
                           //get customer Id using customer_code meta value
                            $users = get_user_id_by_custom_meta_value($_c_meta["IDCUST"]);

                            foreach ($users as $user) {
                            	$userId = $user->ID;
                                echo "<pre>";
                                print_r($userId);
                                echo "</pre>";

                               
                                echo "<pre>";
                                print_r($_c_meta["IDCUST"]);
                                echo "</pre>";

                            	if ($userId) {
                                
	                            	$total_customer_discount_pricelist = count($_c_meta["ezms_ezcatesegrate_customer"]);

	                                update_user_meta($userId, "discount_type", $_c_meta["CMNTTYPE"]);
                                    update_user_meta($userId, "_discount_type", "field_652e7a9108364");
	                                update_user_meta($userId, "discount_level_on_price", $total_customer_discount_pricelist);
                                    update_user_meta($userId, "_discount_level_on_price", "field_652e7b0008365");

	                                $customer_discount_pricelist = $_c_meta["ezms_ezcatesegrate_customer"];

	                                

	                                foreach ($customer_discount_pricelist as $keylist => $_c_pricelist) {

	                                    //dd($_c_pricelist["TYPECD"]);

	                                   
	                                   update_user_meta($userId, 'discount_level_on_price_'.$keylist.'_cateseg', $_c_pricelist['CATESEG']);
                                       update_user_meta($userId, '_discount_level_on_price_'.$keylist.'_cateseg', "field_652e92f98b219");

	                                   update_user_meta($userId, 'discount_level_on_price_'.$keylist.'_sale_price_level', $_c_pricelist['PRICELEVEL']);
                                       update_user_meta($userId, '_discount_level_on_price_'.$keylist.'_sale_price_level', "field_652e930e8b21a");

	                                   update_user_meta($userId, 'discount_level_on_price_'.$keylist.'_sale_price_discount', $_c_pricelist['PRICELEVEL']);
                                       update_user_meta($userId, '_discount_level_on_price_'.$keylist.'_sale_price_discount', "field_652e92b58b218");

	                                   update_user_meta($userId, 'discount_level_on_price_'.$keylist.'_reg_price_level', $_c_pricelist['REGPRICELEVEL']);
                                       update_user_meta($userId, '_discount_level_on_price_'.$keylist.'_reg_price_level', "field_652e7d420836b");

	                                   update_user_meta($userId, 'discount_level_on_price_'.$keylist.'_reg_price_discount', $_c_pricelist['DISPER']);
                                       update_user_meta($userId, '_discount_level_on_price_'.$keylist.'_reg_price_discount', "field_654634255b622");


	                                }

                            	}
                            }

                            

                        }
                    }
                    $total = microtime(true) - $start;
                    echo "<span style='color:red;font-weight:bold'>Total Execution Time: </span>" . $total;

                    // API endpoint
                    // $apiUrl = 'https://modern.cansoft.com/db-clone/api/arcmm/update?key=58fff5F55dd444967ddkhzf';
                    
                    // // List of update IDs
                    // $updateIds = implode(",", $api_ids);
                    
                    // // Prepare the request payload
                    // $requestData = [
                    //     'id' => $updateIds,
                    //     'status' => 'Synced',
                    // ];

                    // // Use cURL to make the API request
                    // $ch = curl_init($apiUrl);
                    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    // curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestData));
                    // $response = curl_exec($ch);

                    // //$total = microtime(true) - $start;

                    // // Check for errors or process the response as needed
                    // if ($response === false) {
                    //     // Handle cURL error
                    //     echo 'cURL Error: ' . curl_error($ch);
                    // } else {
                    //     // Process the API response
                    //     // $response contains the API response data
                    //     echo 'API Response: ' . $response;
                    // }

                    // // Close the cURL session
                    // curl_close($ch);


                    if(! count( $all_customer_meta )){
                        wp_redirect( admin_url( "/users.php?page=mbdcm-sync" ) );
                        exit();
                    }
                }


                // if (isset($_POST['mb-icpricp-product-sync-menual'])) {

                //     mb_customer_meta_sync(1);
                //     wp_redirect( admin_url( "/edit.php?page=mb-customer-sync" ) );
                //     exit();
                // }


                //It work when Click Strt cron  button
                if(isset($_POST['mb-arcmm-sync-cron'])){
                    if (!wp_next_scheduled('mb_arcmm_add_with_cron')) {
                        wp_schedule_event(time(), 'every_twelve_hours', 'mb_arcmm_add_with_cron');
                    }
                    wp_redirect( admin_url( "/admin.php?page=mbdcm-sync" ) );
                    exit();
                }

            ?>
        </div>
    <?php 
}

//For clear cron schedule
function woo_customer_meta_syncronization_apis_plugin_deactivation(){
    wp_clear_scheduled_hook('mb_arcmm_add_with_cron');
    
}
register_deactivation_hook(__FILE__, 'woo_customer_meta_syncronization_apis_plugin_deactivation');


// This happend when icitem caron job is runnning


// This happend when icpricp caron job is runnning

function mb_run_cron_for_arcmm_table(){

    $start = microtime(true);

    mb_customer_meta_sync(1);

    $total = microtime(true) - $start;

    $total = "Total execution time is ". $total;
    
    file_put_contents(plugin_dir_path(__FILE__) . 'cron_debug.log', $total, FILE_APPEND);
    
}

add_action('mb_arcmm_add_with_cron', 'mb_run_cron_for_arcmm_table');

