<?php 

/**
 * Delete woocommerce product by id
 */

function mb_customer_meta_sync($page = 1) {

    $all_customer_meta = fetch_all_customer_metadat_form_arcmn_table($page);

        // $start = microtime(true);
        $arraychunk = array_chunk($all_customer_meta, 2);

        foreach ($arraychunk as $all_metas) {
       
            foreach($all_metas as $_c_meta){
                
                //dd($_c_meta);
               
               //get customer Id using customer_code meta value
                $users = get_user_id_by_custom_meta_value($_c_meta["IDCUST"]);

                if (count($users)) {
                    foreach ($users as $user) {
                        $userId = $user->ID;

                        if ($userId) {
                            
                        $total_customer_discount_pricelist = count($_c_meta["ezms_ezcatesegrate_customer"]);

                            update_user_meta($userId, "discount_type", $_c_meta["CMNTTYPE"]);
                            update_user_meta($userId, "discount_level_on_price", $total_customer_discount_pricelist);

                            $customer_discount_pricelist = $_c_meta["ezms_ezcatesegrate_customer"];

                            

                            foreach ($customer_discount_pricelist as $keylist => $_c_pricelist) {
                                
                                update_user_meta($userId, 'discount_level_on_price_'.$keylist.'_cateseg', $_c_pricelist['CATESEG']);
                                update_user_meta($userId, 'discount_level_on_price_'.$keylist.'_sale_price_level', $_c_pricelist['PRICELEVEL']);
                                update_user_meta($userId, 'discount_level_on_price_'.$keylist.'_sale_price_discount', $_c_pricelist['PRICELEVEL']);
                                update_user_meta($userId, 'discount_level_on_price_'.$keylist.'_reg_price_level', $_c_pricelist['REGPRICELEVEL']);
                                update_user_meta($userId, 'discount_level_on_price_'.$keylist.'_reg_price_discount', $_c_pricelist['DISPER']);


                            }

                        }

                    }
                }
            }
        }
        // $total = microtime(true) - $start;
        // echo "<span style='color:red;font-weight:bold'>Total Execution Time: </span>" . $total;

        // // API endpoint
        // $apiUrl = 'https://modern.cansoft.com/db-clone/api/iciloc/update?key=58fff5F55dd444967ddkhzf';
        
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

    if (count($all_customer_meta)) {

            mb_customer_meta_sync($page);

        }
}