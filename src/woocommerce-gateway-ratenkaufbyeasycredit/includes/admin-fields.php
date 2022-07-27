<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

    'api_signature' => array(
        'title'       => __( 'API Signature (if activated in partner portal, optional)', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'text',
        'description' => __( 'The API signature secures the data transfer against data manipulation by third parties. You can activate the API signature in the ratenkauf by easyCredit merchant portal. ', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
    ),

    'api_verify_credentials' => array(
        'title'       => '',
        'type'        => 'button',
        'default'       => __( 'Verify Credentials', 'woocommerce-gateway-ratenkaufbyeasycredit'),
        'class'     => 'button-primary'
    ),

    'debug' => array(
        'title'   => __( 'Debug Logging', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'    => 'checkbox',
        'label'   => __( 'Enable Debug Logging', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'default' => 'no'
    ),

    'marketing' => array(
        'title'       => __( 'Marketing', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'title'
    ),

    'widget_enabled' => array(
        'title'       => __( 'Show widget next to product price', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'checkbox',
        'description' => __( 'Specifies if ratenkauf by easyCredit will be advertised in product detail view', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'desc_tip'    => true,
        'default'     => 'yes'
    ),

    'widget_selector' => array(
        'title'       => __( 'CSS Selector to include widget at product page', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'text',
        'description' => __( 'If the widget is not shown in the right place, please enter a selector which matches your theme.', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'desc_tip'    => true,
        'default'     => '.product .summary .price'
    ),

    'cart_widget_enabled' => array(
        'title'       => __( 'Show widget in Cart', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'checkbox',
        'description' => __( 'Specifies if ratenkauf by easyCredit will be advertised in cart view', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'desc_tip'    => true,
        'default'     => 'yes'
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

    'clickandcollect' => array(
        'title'       => __( 'Click & Collect', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'title'
    ),

    'clickandcollect_intro' => array(
        'type'        => 'clickandcollectintro'
    ),

    'clickandcollect_shipping_method' => array(
        'title'       => __( 'Shipping method', 'woocommerce-gateway-ratenkaufbyeasycredit' ),
        'type'        => 'select',
        'default'     => '',
        'options'     => array(''=>'')
    ),
);
