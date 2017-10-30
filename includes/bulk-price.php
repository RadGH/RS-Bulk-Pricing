<?php

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Custom Post Type
 */
function rsbp_register_post_type() {
	
	$labels = array(
		'name'                  => 'Bulk Prices',
		'singular_name'         => 'Bulk Price',
		'menu_name'             => 'Bulk Pricing',
		'name_admin_bar'        => 'Bulk Price',
		'archives'              => 'Bulk Price Archives',
		'attributes'            => 'Bulk Price Attributes',
		'parent_item_colon'     => 'Parent Bulk Price:',
		'all_items'             => 'Bulk Pricing',
		'add_new_item'          => 'Add New Bulk Price',
		'add_new'               => 'Add New',
		'new_item'              => 'New Bulk Price',
		'edit_item'             => 'Edit Bulk Price',
		'update_item'           => 'Update Bulk Price',
		'view_item'             => 'View Bulk Price',
		'view_items'            => 'View Bulk Prices',
		'search_items'          => 'Search Bulk Price',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Insert into Bulk Price',
		'uploaded_to_this_item' => 'Uploaded to this Bulk Price',
		'items_list'            => 'Bulk Prices list',
		'items_list_navigation' => 'Bulk Prices list navigation',
		'filter_items_list'     => 'Filter Bulk Prices list',
	);
	$args = array(
		'label'                 => 'Bulk Pricing',
		'description'           => 'WooCommerce products can specify a bulk price among these items.',
		'labels'                => $labels,
		'supports'              => array( 'title', 'author', 'revisions', ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => 'edit.php?post_type=product',
		'show_in_admin_bar'     => false,
		'show_in_nav_menus'     => false,
		'can_export'            => true,
		'has_archive'           => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => false,
		'rewrite'               => false,
		'capability_type'       => 'page',
	);
	register_post_type( 'rs_bulk_price', $args );
	
}
add_action( 'init', 'rsbp_register_post_type', 0 );

/**
 * Add a new Bulk Pricing tab to product pages
 *
 * @param $tabs
 *
 * @return mixed
 */
function rsbp_add_bulk_price_tab_to_products( $tabs ) {
	$tabs['bulk_pricing'] = array(
		'title' => 'Bulk Discount',
	    'priority' => 50,
	    'callback' => 'rsbp_bulk_price_tab_content',
	);
	
	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'rsbp_add_bulk_price_tab_to_products', 20 );

/**
 * Display bulk pricing tab content
 *
 * @param $tab_key
 * @param $tab_data
 */
function rsbp_bulk_price_tab_content( $tab_key, $tab_data ) {
	?>
	<h2><?php echo esc_html($tab_data['title']); ?></h2>
	<?php
	
	$product = wc_get_product( get_the_ID() );
	$product_price = $product->get_price();
	
	$bulk_price_id = get_post_meta( get_the_ID(), '_bulk_price_id', true );
	$price_tiers = $bulk_price_id ? rsbp_get_bulk_price_tiers( $bulk_price_id ) : false;
	
	if ( $price_tiers ) {
		?>
		<table class="bulk-prices">
			<thead>
			<tr>
				<th class="range">Quantity</th>
				<th class="amount">Price</th>
			</tr>
			</thead>
			
			<tbody>
				<?php
				// If price tier does not start from 1, show price for 1-N using default price.
				if ( $price_tiers[0]['min'] > 1 ) {
					$start = 1;
					$end = $price_tiers[0]['min'] - 1;
					?>
					<tr>
						<td class="range"><?php echo rsbp_make_range($start, $end); ?></td>
						<td class="amount"><?php echo wc_price($product_price); ?> ea.</td>
					</tr>
					<?php
				}
				
				foreach( $price_tiers as $k => $v ) {
					$start = $v['min'];
					$end = $v['max'];
					$amount = $product_price - $v['amount'];
					
					?>
					<tr>
						<td class="range"><?php echo rsbp_make_range($start, $end); ?></td>
						<td class="amount"><?php echo wc_price($amount); ?> ea.</td>
					</tr>
					<?php
				}
				?>
			</tbody>
			
		</table>
		<?php
	}else{
		?>
		<p><em>This product is not eligible for bulk discounts.</em></p>
		<?php
	}
}

/**
 * Display a range between two numbers.
 *
 *  IN: 3, 5
 *  OUT: 3 – 5
 *
 *  IN: 5, 5
 *  OUT: 5
 *
 *  IN: 5, (blank)
 *  OUT: 5+
 *
 * @param $start_number
 * @param $end_number
 *
 * @return string
 */
function rsbp_make_range( $start_number, $end_number ) {
	if ( empty($end_number) ) return $start_number . '+';
	else if ( $start_number === $end_number ) return $start_number;
	else return $start_number . ' &ndash; ' . $end_number;
}

/**
 * Returns bulk price data as an array, or false if not valid.
 *
 * @param $bulk_price_id
 *
 * @return bool|mixed|null
 */
function rsbp_get_bulk_price_tiers( $bulk_price_id ) {
	$tiers = get_field( 'discount_tiers', $bulk_price_id );
	
	foreach( $tiers as $k => $v ) {
		if ( $v['amount'] < 0.01 ) {
			unset($tiers[$k]);
		}
	}
	
	return $tiers ? $tiers : false;
}

/**
 * Sorts price tiers by minimum values
 *
 * @param $a
 * @param $b
 *
 * @return int
 */
function _rsbp_sort_price_tiers( $a, $b ) {
	if ($a['min'] == $b['min']) return 0;
	else return ($a['min'] < $b['min']) ? -1 : 1;
}