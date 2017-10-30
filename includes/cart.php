<?php

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Adjust the total price of a product
 * @param $cart
 */
function rsbp_update_cart_item_price( $cart ) {
	foreach ( $cart->get_cart_contents() as $cart_item_key => $value ) {
		$discount = ld_woo_get_item_data( $cart_item_key, 'bulk_discount' );
		
		
		$value['data']->set_price( $value['data']->get_price() - $discount );
	}
}
add_action( 'woocommerce_before_calculate_totals', 'rsbp_update_cart_item_price' );

/**
 * Update bulk discount when added to cart
 *
 * @param $cart_item_key
 * @param $product_id
 * @param $quantity
 * @param $variation_id
 * @param $variation
 * @param $cart_item_data
 */
function rsbp_add_bulk_discount_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
	$discount = rsbp_get_bulk_discount_amount( $product_id, $quantity );
	
	ld_woo_set_item_data( $cart_item_key, 'bulk_discount', $discount );
	ld_woo_set_item_data( $cart_item_key, 'bulk_discount_quantity', $quantity );
}
add_action( 'woocommerce_add_to_cart', 'rsbp_add_bulk_discount_to_cart', 10, 6 );

/**
 * Update bulk discount when quantity changes
 *
 * @param $cart_item_key
 * @param $quantity
 * @param $old_quantity
 */
function rsbp_update_bulk_discount_after_quantity_change( $cart_item_key, $quantity, $old_quantity ) {
	$cart_item = WC()->cart->get_cart_item($cart_item_key);
	
	$discount = rsbp_get_bulk_discount_amount( $cart_item['product_id'], $quantity );
	
	ld_woo_set_item_data( $cart_item_key, 'bulk_discount', $discount );
	ld_woo_set_item_data( $cart_item_key, 'bulk_discount_quantity', $quantity );
}
add_action( 'woocommerce_after_cart_item_quantity_update', 'rsbp_update_bulk_discount_after_quantity_change', 10, 3 );

/**
 * Gets the discount amount for a product based on the quantity in the cart.
 *
 * @param $product_id
 * @param $quantity
 *
 * @return bool
 */
function rsbp_get_bulk_discount_amount( $product_id, $quantity ) {
	$product = wc_get_product( $product_id );
	$original_price = $product->get_price();
	
	$bulk_price_id = get_post_meta( $product_id, '_bulk_price_id', true );
	$price_tiers = $bulk_price_id ? rsbp_get_bulk_price_tiers( $bulk_price_id ) : false;
	
	// No bulk discounts for this item
	if ( empty($price_tiers) ) return false;
	
	foreach( $price_tiers as $p ) {
		$min = $p['min'];
		$max = $p['max'];
		$amount = $p['amount'];
		
		// Ignore bulk discounts for the original price
		if ( empty($amount) || ((float) $amount === (float) $original_price) ) continue;
		
		// If within this tier, return the discount amount.
		if ( $quantity >= $min && ( empty($max) || $max >= $quantity ) ) {
			return $amount;
		}
	}
	
	// Quantity not within bulk discount tier
	return false;
}

/**
 * Display data on cart/checkout pages
 *
 * @param $data
 * @param $cart_item
 *
 * @return array
 */
function rsbp_display_cart_item_meta( $data, $cart_item ) {
	$discount = ld_woo_get_item_data( $cart_item['key'], 'bulk_discount' );
	$quantity = ld_woo_get_item_data( $cart_item['key'], 'bulk_discount_quantity' );
	
	if ( $discount ) {
		$data[] = array(
			'name' => 'Bulk Savings',
			'value' => wc_price($discount * $quantity)
		);
	}
	
	return $data;
}
add_filter( 'woocommerce_get_item_data', 'rsbp_display_cart_item_meta', 10, 2 );

/**
 * Convert cart item data to order item metadata when checkout is complete
 *
 * @param $item_id
 * @param $values
 * @param $cart_item_key
 */
function rsbp_display_order_item_meta( $item_id, $values, $cart_item_key ) {
	$discount = ld_woo_get_item_data( $cart_item_key, 'bulk_discount' );
	
	if ( $discount ) wc_add_order_item_meta( $item_id, 'bulk_discount', $discount );
}
add_action( 'woocommerce_add_order_item_meta', 'rsbp_display_order_item_meta', 10, 3 );

/**
 * Convert cart item data to order item metadata when checkout is complete
 *
 * @param $meta
 * @param $order_item
 *
 * @return mixed
 */
function rsbp_customize_order_meta_label( $meta, $order_item ) {
	if ( $meta ) foreach( $meta as $k => $v ) {
		if ( $v->key === 'bulk_discount' ) {
			$meta[$k]->display_key = 'Bulk Discount (per item)';
			$meta[$k]->display_value = wc_price($meta[$k]->value);
		}
	}
	
	return $meta;
}
add_action( 'woocommerce_order_item_get_formatted_meta_data', 'rsbp_customize_order_meta_label', 10, 3 );