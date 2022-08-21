<?php 
session_start();

register_nav_menus(array('menu' => 'Menu'));
add_post_type_support('page', 'excerpt');
add_theme_support( 'post-thumbnails' );
add_theme_support(  'automatic-feed-links' );

add_theme_support( 'wc-product-gallery-zoom' );
add_theme_support( 'wc-product-gallery-lightbox' );
add_theme_support( 'wc-product-gallery-slider' );

add_image_size('landing', 360, 270, true);

// WOOCOMMERCE CONFIG
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
add_action('woocommerce_before_main_content', 'my_theme_wrapper_start', 10);
add_action('woocommerce_after_main_content', 'my_theme_wrapper_end', 10);

function my_theme_wrapper_start() {
  echo '<div class="">';
}

function my_theme_wrapper_end() {
  echo '</div>';
}

function mytheme_add_woocommerce_support() {
	add_theme_support( 'woocommerce' );
}
add_action( 'after_setup_theme', 'mytheme_add_woocommerce_support' );

add_action( 'widgets_init', 'theme_slug_widgets_init' );
function theme_slug_widgets_init() {
    register_sidebar( array(
        'name' => __( 'Blog sidebar', 'theme-slug' ),
        'id' => 'sidebar-1',
        'description' => __( 'Widgets se muestran en la pagina de blog.', 'theme-slug' ),
        'before_widget' => '<ul id="%1$s" class="widget %2$s">',
	'after_widget'  => '</ul>',
	'before_title'  => '<h2 class="widgettitle">',
	'after_title'   => '</h2>',
    ) );

    register_sidebar( array(
      'name' => __( 'Woo sidebar', 'theme-slug' ),
      'id' => 'sidebar-2',
      'description' => __( 'Widgets se muestran en la pagina de tienda.', 'theme-slug' ),
      'before_widget' => '<ul id="%1$s" class="widget %2$s">',
'after_widget'  => '</ul>',
'before_title'  => '<h2 class="widgettitle">',
'after_title'   => '</h2>',
  ) );
}

add_action( 'woocommerce_before_add_to_cart_quantity', 'bbloomer_echo_qty_front_add_cart' );
function bbloomer_echo_qty_front_add_cart() {
 echo '<div style="margin-top: 8px;"> Cantidad :  </div>'; 
}

// https://maps.googleapis.com/maps/api/geocode/json?latlng=-33.4285328,-70.61734659999999&sensor=true&key=AIzaSyDGbJE7Em1U-vpuzhZLhZrdA9iydaC7eOU

function custom_override_checkout_fields( $fields ) {	
	unset( $fields['billing']['billing_company'] );
	unset( $fields['billing']['billing_address_1'] );
	unset( $fields['billing']['billing_address_2'] );
	unset( $fields['billing']['billing_city'] );
  unset( $fields['billing']['billing_postcode'] );
  unset( $fields['billing']['billing_state'] );
  unset( $fields['billing']['billing_country'] );
  
  
	return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );



add_filter('woocommerce_billing_fields', 'custom_woocommerce_billing_fields');

function custom_woocommerce_billing_fields($fields)
{

    $fields['billing_options'] = array(
        'label' => __('Rut Empresa', 'woocommerce'), // Add custom field label
        'placeholder' => _x('Ingrese sólo números, sin puntos ni guión: 123456789', 'placeholder', 'woocommerce'), // Add custom field placeholder
        'required' => true, // if field is required or not
        'type' => 'number', // add field type
        'priority' => 1000,
		'desc_tip' => 'true',
		'description' => _x('Ingrese sólo números, sin puntos ni guión: 123456789', 'placeholder', 'woocommerce'),
    );

    return $fields;
}

function my_woocommerce_add_error( $error ) {
  return str_replace('Facturación','Cotización ',$error);    
}
add_filter( 'woocommerce_add_error', 'my_woocommerce_add_error' );

add_action( 'woocommerce_before_shop_loop_item_title', 'bbloomer_display_sold_out_loop_woocommerce' );
 
function bbloomer_display_sold_out_loop_woocommerce() {
    global $product;
 
    if ( !$product->is_in_stock() ) {
        echo '<span class="soldout">AGOTADO</span>';
    }
} 

add_action('init', 'woocommerce_clear_cart_url');
function woocommerce_clear_cart_url() {
    global $woocommerce;
    if( isset($_REQUEST['clear-cart']) ) {
        $woocommerce->cart->empty_cart();
    }
}

add_action( 'woocommerce_admin_order_data_after_billing_address', 'radar_checkout_field_display_admin_order_meta', 10, 1 );
function radar_checkout_field_display_admin_order_meta( $order ) {
  echo '<p><strong>Rut Empresa:</strong> ' . get_post_meta( $order->id, 'billing_options', true ) . '</p>';
}

  add_action( 'woocommerce_checkout_update_order_meta', 'radar_custom_checkout_field_update_user_meta' );
function radar_custom_checkout_field_update_user_meta( $order_id ) {
if ( $_POST['billing_options'] ) update_post_meta( $order_id, 'billing_options', sanitize_text_field($_POST['billing_options']) );
}

add_filter( 'woocommerce_email_order_meta_fields', 'custom_woocommerce_email_order_meta_fields', 10, 3 );
function custom_woocommerce_email_order_meta_fields( $fields, $sent_to_admin, $order ) {
    $fields['rut'] = array( 'label' => __( 'Rut Empresa' ), 'value' => get_post_meta( $order->id, 'billing_options', true ), );
    
    return $fields;
}

add_action( 'woocommerce_single_product_summary', 'dev_designs_show_sku', 5 );
function dev_designs_show_sku(){
    global $product;
    echo 'SKU: aaa' . $product->get_sku();
}

add_action( 'woocommerce_single_product_summary', 'dev_designs_show_price', 20 );
function dev_designs_show_price(){
    global $product;
	if($product->get_price() ==! NULL ) {
    	echo 'Precio por unidad: $' . $product->get_price() . '+ IVA'; 
	}
}

add_filter( 'get_product_search_form' , 'woo_custom_product_searchform' );
function woo_custom_product_searchform( $form ) {

	$form = '<form role="search" method="get" id="searchform" action="' . esc_url( home_url( '/'  ) ) . '">
		<div>
			<label class="screen-reader-text" for="s">' . __( 'Buscar...', 'woocommerce' ) . '</label>
			<input class="buscador-header" type="text" value="' . get_search_query() . '" name="s" id="s" />

			<input type="hidden" name="post_type" value="product" />
		</div>
	</form>';

	return $form;

}

//12 Productos por búsqueda 
add_action( 'pre_get_posts', function( $query ) {

    if( $query->is_main_query() && ! is_admin() && $query->is_search() ) {
        $query->set( 'posts_per_page', 12 );
    }

} );



?>