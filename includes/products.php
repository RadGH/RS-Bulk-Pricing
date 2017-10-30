<?php

if ( !defined( 'ABSPATH' ) ) exit;

// Display the "Bulk Pricing Group" dropdown on product pages
function rsbp_add_bulk_price_select_field() {
	global $woocommerce, $post;
	
	$args = array(
		'post_type' => 'rs_bulk_price',
	    'orderby' => 'title',
	    'nopaging' => true,
	);
	
	$bulk_prices = new WP_Query( $args );
	
	$bulk_price_id = get_post_meta( $post->ID, '_bulk_price_id', true );
	
	echo '<div class="options_group">';
	
	// Product Select
	?>
	<p class="form-field bulk_price_id">
		<label for="bulk_price_id"><?php echo 'Bulk Pricing Group'; ?></label>
		<select id="bulk_price_id" name="bulk_price_id">
			<option value="">&ndash; No bulk pricing &ndash;</option>
			<?php
			if ( $bulk_prices->have_posts() ) while( $bulk_prices->have_posts() ): $bulk_prices->the_post();
				$opt_value = get_the_ID();
				$opt_name = get_the_title();
				?>
				<option value="<?php echo esc_attr( $opt_value ); ?>" <?php selected( $bulk_price_id, $opt_value ); ?>><?php echo esc_html( $opt_name ); ?></option>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</select> <img class="help_tip" data-tip='<?php echo 'To add or modify bulk pricing groups, use the Bulk Pricing menu in the left sidebar.' ?>' src="<?php echo $woocommerce->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
	</p>
	<?php
	
	echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'rsbp_add_bulk_price_select_field' );

// Save the bulk pricing group field to the product
function rsbp_save_bulk_price_select_field( $post_id ) {
	$bulk_price_id = isset($_POST['bulk_price_id']) ? stripslashes($_POST['bulk_price_id']) : false;
	
	if( $bulk_price_id ) {
		update_post_meta( $post_id, '_bulk_price_id', $bulk_price_id );
	}
}
add_action( 'woocommerce_process_product_meta', 'rsbp_save_bulk_price_select_field' );