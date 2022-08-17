<?php

// Start Steadfast API System
//
add_action('woocommerce_update_order', 'custom_order_update', 10, 2);
function custom_order_update($order_id, $order)
{

    if ($order->get_status() == 'on-hold') {

        $curl = curl_init();
        /**
         * API KEY from steadfast courier
         *  'Api-Key: dfgfdgfdgdfhgfdhgfhjgfjhgfh',
         *  'Secret-Key: dfgdfgfdghfdhfdfgd',
         */
        $header = array(
            'Api-Key: dfgfdgfdgdfhgfdhgfhjgfjhgfh',
            'Secret-Key: dfgdfgfdghfdhfdfgd',
            'Content-Type: application/json',
        );
        /*Docs:  https://www.businessbloomer.com/woocommerce-easily-get-order-info-total-items-etc-from-order-object/
         */
        $shipping_address_1 = $order->get_shipping_address_1();
        $country = $order->get_billing_country();
        $state = $order->get_billing_state();
        $city = $order->get_shipping_city();
        $district = WC()->countries->get_states($country)[$state];

        $body = '{
            "invoice": "' . 0 . $order_id . '",
            "recipient_name": "' . $order->get_formatted_shipping_full_name() . '",
            "recipient_phone": "' . $order->get_billing_phone() . '",
            "recipient_address": "' . $shipping_address_1 . ', ' . $city . ', ' . $district . '",
            "cod_amount": "' . $order->get_total() . '",
            "note":"' . $order->get_customer_note() . '"
        }';

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://portal.steadfast.com.bd/api/v1/create_order',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $header,
        ));

        $response = curl_exec($curl);

        $data = json_decode($response, true);
        if ($data['status'] == 200) {
            $consignment_id = $data['consignment']['consignment_id'];
            $tracking_code = $data['consignment']['tracking_code'];
            // save this value to your desired place
            //
            add_post_meta($order_id, 'consignment_id', $consignment_id);
        }

        curl_close($curl);

    }

}
// End Steadfast API System
