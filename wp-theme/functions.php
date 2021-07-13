<?php

// ACF options
require_once('inc/config/acf-options.php');

// custom functions
require_once('inc/config/custom-functions.php');

// scripts
require_once('inc/config/scripts.php');

// Stripe PK

function pub_key() {
	$publishable_key = get_field('stripe_live_publishable_key', 'options');
	if (empty($publishable_key)){
		$publishable_key = get_field('sandbox_publishable_key', 'options');
	}
	echo '<script>
        var pk = "'.$publishable_key.'";
    </script>';
}

add_action('wp_head', 'pub_key', 1);

// Shortcode, return Stripe form

add_shortcode('credit-report-form', 'shortcode_stripeform');

function shortcode_stripeform() {
	// Shortcode form scripts

	wp_enqueue_style( 'global-stripe-styles', get_stylesheet_directory_uri() . '/stripe/global.css' );
	wp_enqueue_script( 'stripe-v3', 'https://js.stripe.com/v3/');
	wp_enqueue_script( 'polyfill', 'https://polyfill.io/v3/polyfill.min.js?version=3.52.1&features=fetch');
	wp_enqueue_style( 'additional-css-payment-form', get_stylesheet_directory_uri() . '/stripe/form.css' );
	wp_enqueue_script( 'stripe-client', get_stylesheet_directory_uri() . '/stripe/client.js' );

	// Shortcode form

	ob_start();

	get_template_part('stripe/form');

	return ob_get_clean();
}

// Custom post type - Credit reports orders

add_action('init', 'credit_reports_orders_posttype');
function credit_reports_orders_posttype() {
	register_post_type('orders', array(
		'labels' => array(
			'name' => 'Credit reports orders',
			'singular_name' => 'Order',

		),
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'query_var' => true,
		'rewrite' => true,
		'capability_type' => 'post',
		'has_archive' => true,
		'hierarchical' => false,
		'menu_position' => null,
		'supports' => array('title')
	));
}

// Add custom post

add_action('wp_head', 'myplugin_ajaxurl');

function myplugin_ajaxurl() {
	echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}

add_action('wp_ajax_add_reportes_orders', 'add_reportes_orders');
add_action('wp_ajax_nopriv_add_reportes_orders', 'add_reportes_orders');

function add_reportes_orders() {

	if (isset($_POST['order_id'])) {

		$order_id = $_POST['order_id'];
		$names = $_POST['names'];
		$quantity = $_POST['quantity'];
		$email = $_POST['email'];

		$price = 25 * $quantity;

		// Create Post

		$new_post = array(
			'ID' => '',
			'post_type' => 'orders',
			'post_status' => 'publish',
			'post_title' => "Stripe Payment Reference ID: " . $order_id,
		);

		$post_id = wp_insert_post($new_post);
		$post = get_post($post_id);

		$new_post_title = 'Order ID: ' . $post_id . ' — Stripe Payment Reference ID: ' . $order_id;
		wp_update_post(
			array(
				'ID' => $post_id,
				'post_title' => $new_post_title
			)
		);

		// Report Order Fields

		update_field('number_of_credit_reports_ordered', $quantity, $post_id);
		update_field('names', $names, $post_id);
		update_field('stripe_payment_reference_number', $order_id, $post_id);

		$email_body = ''; // Here our email template

		$admin_email = get_option('admin_email');

		$to = $email.','.$admin_email;
		$subject = 'Credit report order';
		$headers = "From: ".$admin_email."\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		$body = $email_body;
		mail($to, $subject, $body, $headers);
	}
}

// ACF Admin Page Payment form

add_action('acf/init', 'my_acf_op_init');
function my_acf_op_init() {

	// Check function exists.
	if( function_exists('acf_add_options_page') ) {

		// Register options page.
		$option_page = acf_add_options_page(array(
			'page_title'    => __('Payment form'),
			'menu_title'    => __('Payment form'),
			'menu_slug'     => 'payment-form',
			'capability'    => 'edit_posts',
			'redirect'      => false
		));
	}
}