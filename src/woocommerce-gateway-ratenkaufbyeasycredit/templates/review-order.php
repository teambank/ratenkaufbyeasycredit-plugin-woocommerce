<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<p><?php _e('Please review and confirm your order','woocommerce-gateway-ratenkaufbyeasycredit'); ?></p>

<?php wc_get_template( 'order/order-details-customer.php', array( 'order' => $order ) ); ?>

<section class="woocommerce-payment-details">

		<section class="woocommerce-columns woocommerce-columns--2 col2-set addresses">
			<div class="woocommerce-column woocommerce-column--1 col-1">
				
				<h2 class="woocommerce-column__title">
					<?php _e('Payment Method',
					'woocommerce-gateway-ratenkaufbyeasycredit'); 
					?>
				</h2>
			
				<p class="easycredit-info-description">
					<span class="easycredit-info-logo">
						<?php _e('ratenkauf by easyCredit','woocommerce-gateway-ratenkaufbyeasycredit'); ?>
					</span><br />
					<strong><?php _e('Easy. Fair. Pay by installments.', 
						'woocommerce-gateway-ratenkaufbyeasycredit'); ?>
					</strong><br />
					<?php echo $gateway->get_storage()->get('payment_plan'); ?>
				</p>

			</div>

			<div class="woocommerce-column woocommerce-column--2 col-2">
				
				<h2 class="woocommerce-column__title">
					<?php _e('Redemption Plan', 'woocommerce-gateway-ratenkaufbyeasycredit'); ?>
				</h2>
			
				<?php echo $gateway->get_storage()->get('redemption_plan'); ?>
				<br />
				
				<a href="<?php echo esc_url($gateway->get_storage()->get('pre_contract_information_url')); ?>" target="_blank" style="text-decoration:underline !important;">
					<?php echo _e('Click to view precontractual information for ratenkauf by easyCredit.',
						'woocommerce-gateway-ratenkaufbyeasycredit'); ?>
				</a>
			</div>	
		</section>
</section>

<?php $order_items = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) ); ?>
<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">

	<thead>
		<tr>
			<th class="woocommerce-table__product-name product-name"><?php _e( 'Product', 'woocommerce' ); ?></th>
			<th class="woocommerce-table__product-table product-total"><?php _e( 'Total', 'woocommerce' ); ?></th>
		</tr>
	</thead>

	<tbody>
		<?php
			foreach ( $order_items as $item_id => $item ) {
				$product = apply_filters( 'woocommerce_order_item_product', $item->get_product(), $item );

				wc_get_template( 'order/order-details-item.php', array(
					'order'			     => $order,
					'item_id'		     => $item_id,
					'item'			     => $item,
					'show_purchase_note' => $show_purchase_note,
					'purchase_note'	     => $product ? $product->get_purchase_note() : '',
					'product'	         => $product,
				) );
			}
		?>
		<?php do_action( 'woocommerce_order_items_table', $order ); ?>
	</tbody>

	<tfoot>
		<?php
			$totals = apply_filters(
				'woocommerce_ratenkaufbyeasycredit_order_item_totals',
				$order
			);
			foreach ($totals as $key => $total ) {
				?>
				<tr>
					<th scope="row"><?php echo $total['label']; ?></th>
					<td><?php echo $total['value']; ?></td>
				</tr>
				<?php
			}
		?>
		<?php if ( $order->get_customer_note() ) : ?>
			<tr>
				<th><?php _e( 'Note:', 'woocommerce' ); ?></th>
				<td><?php echo wptexturize( $order->get_customer_note() ); ?></td>
			</tr>
		<?php endif; ?>
	</tfoot>
</table>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( $gateway->get_confirm_url() ); ?>" enctype="multipart/form-data">
	<div class="ratenkaufbyeasycredit checkout-review-button">
	
		<?php do_action( 'woocommerce_pay_order_before_submit' ); ?>
		
		<?php $order_button_text = apply_filters( 
			'woocommerce_pay_order_button_text',
			__( 'Place order', 'woocommerce' )
		); ?>
	
		<?php echo apply_filters( 'woocommerce_pay_order_button_html', '<input name="woo-'.$gateway->id.'-confirm" type="submit" class="button alt" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '" />' ); ?>
	
		<?php do_action( 'woocommerce_pay_order_after_submit' ); ?>
		<?php wp_nonce_field( 'woocommerce-easycredit-pay' ); ?>
	</div>
</form>
