<?php
return array(
    'enabled' => array(
        'title'   => __( 'Enable/Disable', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'    => 'checkbox',
        'label'   => __( 'Enable ratenkauf by easyCredit Payment', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'default' => 'yes'
    ),

    'display_settings' => array(
        'title'       => __( 'Display settings', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'title'
    ),

    'title' => array(
        'title'       => __( 'Title', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'text',
        'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'desc_tip'    => true,
    ),

    'widget_enabled' => array(
        'title'       => __( 'Show widget next to product price', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'checkbox',
        'description' => __( 'Specifies if ratenkauf by easyCredit will be advertised in product detail view', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'desc_tip'    => true,
        'default'     => 'yes'
    ),


    'cart_widget_enabled' => array(
        'title'       => __( 'Show widget in Cart', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'checkbox',
        'description' => __( 'Specifies if ratenkauf by easyCredit will be advertised in cart view', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'desc_tip'    => true,
        'default'     => 'yes'
    ),

/*
    'description' => array(
        'title'       => __( 'Description', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'textarea',
        'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'default'     => __( 'You will be redirected to ratenkauf by easyCredit once you submit the order', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'desc_tip'    => true,
    ),
*/
    'instructions' => array(
        'title'       => __( 'Instructions', 'woocommerce' ),
        'type'        => 'textarea',
        'description' => __( 'Instructions that will be added to the thank you page and emails.', 'woocommerce' ),
        'default'     => '',
        'desc_tip'    => true,
    ),

    'api_details' => array(
        'title'       => __( 'API credentials', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'title'
    ),
    'api_key' => array(
        'title'       => __( 'API Key', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'text',
    ),

    'api_token' => array(
        'title'       => __( 'API Token', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'text',
    ),

    'api_verify_credentials' => array(
        'title'       => '',
        'type'        => 'button',
        'default'       => __( 'Verify Credentials', 'woocommerce-gateway-ratenkaufbyeasycredit'),
        'class'     => 'button-primary'
    ),
    /*
    'processing' => array(
        'title'       => __( 'Processing settings', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'title'
    ),
    'order_status' => array(
        'title'   => __( 'Order status', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'    => 'select',
    ), 
    'pamyent_status' => array(
        'title'   => __( 'Payment status', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'    => 'select',
    ), 
    */
    'advanced' => array(
        'title'       => __( 'Advanced Settings', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'title'
    ),
    'debug' => array(
        'title'   => __( 'Debug Logging', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'    => 'checkbox',
        'label'   => __( 'Enable Debug Logging', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'default' => 'no'
    ),
    'widget_selector' => array(
        'title'       => __( 'CSS Selector to include widget at product page', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'text',
        'description' => __( 'If the widget is not shown in the right place, please enter a selector which matches your theme.', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'desc_tip'    => true,
        'default'     => '.product .summary .price'
    ),
    'cart_widget_selector' => array(
        'title'       => __( 'CSS Selector to include widget at the cart page', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'text',
        'description' => __( 'If the widget is not shown in the right place, please enter a selector which matches your theme.', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'desc_tip'    => true,
        'default'     => '.wc-proceed-to-checkout'
    ),

    'transactions' => array(
        'title'       => __( 'Order Management', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'title'
    ),

    'mark_shipped' => array(
        'title'       => __( 'Confirm shipping automatically?', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'checkbox',
        'default'     => 'no'
    ),
    'mark_shipped_status' => array(
        'title'       => __( 'Order status to confirm shipping', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'select',
        'default'     => 'wc-completed',
        'options'     => wc_get_order_statuses()
    ),

    'mark_refunded' => array(
        'title'       => __( 'Refund automatically?', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'checkbox',
        'default'     => 'no'
    ),
    'mark_refunded_status' => array(
        'title'       => __( 'Order status to refund', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'select',
        'default'     => 'wc-refunded',
        'options'     => wc_get_order_statuses()
    ),
);
