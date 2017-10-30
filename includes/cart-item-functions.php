<?php
/*
Instructions:

	1) Save data when adding to cart

	When adding an item to your cart, use ld_woo_set_item_data. The best action for this to occur is
	"woocommerce_add_to_cart". The first parameter for this action contains the "cart_item_key",
	which is required for the function.

	  // Save a custom field called "class-date" to the item added to the cart
	  $date = $_REQUEST['class-date'];
	  ld_woo_set_item_data( cart_item_key, "class-date", $date );

	2) Retrieve item data before checkout

	At any point you may retrieve this data using "ld_woo_get_item_data". If you wantto get a list of
	all cart items, use: "WC()->cart->get_cart()". This will return an array. The keys are the
	"cart_item_key" which will be needed.

	3) Display data assigned to the item after the order is complete

	When a user checks out, their "cart" is emptied and what they had purchased becomes an "order"
	instead. At this point, all of the custom item data is stored as item meta using the key
	"_ld_woo_product_data".


Functions:

	ld_woo_get_item_data( $cart_item_key, [$key, $default] )
		Returns cart item data for the specified cart item. If $key is provided, a single value is returned.
		Otherwise, an array of all cart item data is returned.

	ld_woo_set_item_data( $cart_item_key, $key, $value )
		Sets the data for a cart item by key. Similar to post meta, but based on session.

	ld_woo_remove_item_data( $cart_item_key, [$key] )
		Removes cart item data, a specific key if $key is provided, otherwise the entire cart item's data variable is removed.
		* Called automatically when product is removed from the cart

*/

/**
 * @param $cart_item_key
 * @param null $key
 * @param null $default
 *
 * @return mixed|null
 */
function ld_woo_get_item_data( $cart_item_key, $key = null, $default = null ) {
	$data = (array)WC()->session->get( '_ld_woo_product_data' );
	if ( empty( $data[ $cart_item_key ] ) ) {
		$data[ $cart_item_key ] = array();
	}
	
	// If no key specified, return an array of all results.
	if ( $key == null ) {
		return $data[ $cart_item_key ] ? $data[ $cart_item_key ] : $default;
	}else{
		return empty( $data[ $cart_item_key ][ $key ] ) ? $default : $data[ $cart_item_key ][ $key ];
	}
}

/**
 * @param $cart_item_key
 * @param $key
 * @param $value
 */
function ld_woo_set_item_data( $cart_item_key, $key, $value ) {
	$data = (array)WC()->session->get( '_ld_woo_product_data' );
	if ( empty( $data[ $cart_item_key ] ) ) {
		$data[ $cart_item_key ] = array();
	}
	
	$data[ $cart_item_key ][ $key ] = $value;
	
	WC()->session->set( '_ld_woo_product_data', $data );
}

/**
 * @param null $cart_item_key
 * @param null $key
 */
function ld_woo_remove_item_data( $cart_item_key = null, $key = null ) {
	$data = (array)WC()->session->get( '_ld_woo_product_data' );
	
	// If no item is specified, delete *all* item data. This happens when we clear the cart (eg, completed checkout)
	if ( $cart_item_key == null ) {
		WC()->session->set( '_ld_woo_product_data', array() );
		
		return;
	}
	
	// If item is specified, but no data exists, just return
	if ( !isset( $data[ $cart_item_key ] ) ) {
		return;
	}
	
	if ( $key == null ) {
		// No key specified, delete this item data entirely
		unset( $data[ $cart_item_key ] );
	}else{
		if ( isset( $data[ $cart_item_key ][ $key ] ) ) {
			unset( $data[ $cart_item_key ][ $key ] );
		}
	}
	WC()->session->set( '_ld_woo_product_data', $data );
}

add_filter( 'woocommerce_before_cart_item_quantity_zero', 'ld_woo_remove_item_data', 10, 1 );
add_filter( 'woocommerce_cart_emptied', 'ld_woo_remove_item_data', 10, 1 );