<?php
/* Add custom functions - Th.M */

// Display message, on checkout page, in the review order block, before the payment options.
function messageBeforePaymentMethods() {
	echo "<div class='jNotice'>";
    	echo esc_html__('Μην προβείτε σε πληρωμή, πριν ενημερωθείτε για τη διαθεσιμότητα του προϊόντος', 'Divi');
	echo "</div>";
}
add_action('woocommerce_review_order_before_payment', 'messageBeforePaymentMethods');

add_action('admin_head', 'my_custom_fonts');
function my_custom_fonts() {
  echo '<style>
	#wp-admin-bar-notes, .wpml-admin-notice,  { display:none; }
</style>';
}

// display an 'Out of Stock' label on archive pages
add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_stock', 10 );
function woocommerce_template_loop_stock() {
    global $product;
    if ( ! $product->managing_stock() && ! $product->is_in_stock() )
        echo '<p class="jr-oof">Εξαντλημένο</p>';
}

// Out of stock all variations
// Single variable produccts pages - Sold out functionality
add_action( 'woocommerce_single_product_summary', 'replace_single_add_to_cart_button', 1 );
function replace_single_add_to_cart_button() {
    global $product;

    // For variable product types
    if( $product->is_type( 'variable' ) ) {
        $is_soldout = true;
        foreach( $product->get_available_variations() as $variation ){
            if( $variation['is_in_stock'] )
                $is_soldout = false;
        }
        if( $is_soldout ){
            remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
            add_action( 'woocommerce_single_variation', 'sold_out_button', 20 );
        }
    }
}

// The sold_out button replacement
function sold_out_button() {
    global $post, $product;

    ?>
    <div class="woocommerce-variation-add-to-cart variations_button">
        <?php
            do_action( 'woocommerce_before_add_to_cart_quantity' );

            woocommerce_quantity_input( array(
                'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
                'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : $product->get_min_purchase_quantity(),
            ) );

            do_action( 'woocommerce_after_add_to_cart_quantity' );
        ?>
        <a class="single_sold_out_button button alt disabled wc-variation-is-unavailable"><?php _e( "Εξαντλημένο", "woocommerce" ); ?></a>
    </div>
    <?php
}





//shortcode for mini-cart
function jma_woo_minicart($atts){  
    ob_start();
    global $woocommerce; 
 
    echo '<a class="cart-contents" href="' . $woocommerce->cart->get_cart_url() . '" title="Δείτε το καλάθι σας">';
    echo sprintf(_n('%d προϊόν', '%d προϊόντα', $woocommerce->cart->cart_contents_count, 'Divi'), $woocommerce->cart->cart_contents_count) . ' - ' . $woocommerce->cart->get_cart_total() . '</a>';
 
    $x = ob_get_contents();
    ob_end_clean();
    return $x;
}
add_shortcode('jma_woo_minicart','jma_woo_minicart');
 
// Ensure cart contents update when products are added to the cart via AJAX (place the following in functions.php)
function woocommerce_header_add_to_cart_fragment( $fragments ) {
	global $woocommerce;
	
	ob_start();
	
	?>
	<a class="cart-contents" href="<?php echo $woocommerce->cart->get_cart_url(); ?>" title="<?php _e('View your shopping cart', 'Divi'); ?>"><?php echo sprintf(_n('%d προϊόν', '%d προϊόντα', $woocommerce->cart->cart_contents_count, 'Divi'), $woocommerce->cart->cart_contents_count);?> - <?php echo $woocommerce->cart->get_cart_total(); ?></a>
	<?php
	
	$fragments['a.cart-contents'] = ob_get_clean();
	
	return $fragments;
	
}
add_filter('add_to_cart_fragments', 'woocommerce_header_add_to_cart_fragment');


function remove_image_zoom_support() {
    remove_theme_support( 'wc-product-gallery-zoom' );
}
add_action( 'wp', 'remove_image_zoom_support', 100 );


add_filter( 'woocommerce_get_item_data', 'displaying_cart_items_weight', 10, 2 );
function displaying_cart_items_weight( $item_data, $cart_item ) {
	$item_weight = $cart_item['data']->get_weight() * $cart_item['quantity'];
    $item_data[] = array(
        'key'       => __('Βάρος', 'woocommerce'),
        'value'     => $item_weight,
        'display'   => $item_weight . ' ' . get_option('woocommerce_weight_unit')
    );

    return $item_data;
}


add_action('woocommerce_before_checkout_form', 'print_cart_weight');
add_action('woocommerce_before_cart', 'print_cart_weight');
 
function print_cart_weight( $posted ) {
global $woocommerce;
$notice = 'Το συνολικό βάρος του καλαθιού σου είναι: ' . $woocommerce->cart->cart_contents_weight . get_option('woocommerce_weight_unit');
if( is_cart() ) {
    wc_print_notice( $notice, 'notice' );
} else {
    wc_add_notice( $notice, 'notice' );
}
}
