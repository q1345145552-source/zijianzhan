<?php

if ( ! class_exists( 'Shopee_Mall_Template_Applier' ) ) {
	fwrite( STDERR, "Shopee_Mall_Template_Applier is missing.\n" );
	exit( 1 );
}

$result = Shopee_Mall_Template_Applier::apply();

if ( is_wp_error( $result ) ) {
	fwrite( STDERR, $result->get_error_message() . "\n" );
	exit( 1 );
}

$front_page_id = (int) get_option( 'page_on_front' );
$front_page    = get_post( $front_page_id );
$settings      = get_option( 'shopee_settings', array() );
$failures      = array();

if ( $front_page_id !== (int) $result ) {
	$failures[] = 'The returned page ID is not the active front page.';
}

if ( ! $front_page || 'publish' !== $front_page->post_status ) {
	$failures[] = 'The front page was not published.';
}

if ( ! $front_page || false === strpos( $front_page->post_content, 'mall-shop-hero' ) ) {
	$failures[] = 'The Mall template content was not applied.';
}

if ( ! $front_page || 'หน้าหลัก' !== $front_page->post_title ) {
	$failures[] = 'The front page title was not changed to Thai.';
}

if ( 'เซียงไท่ มอลล์' !== get_option( 'blogname' ) || 'เซียงไท่ มอลล์' !== ( $settings['shop_name'] ?? '' ) ) {
	$failures[] = 'The Thai shop name was not applied.';
}

if ( 'th' !== get_option( 'WPLANG' ) ) {
	$failures[] = 'The Thai locale was not applied.';
}

if ( $failures ) {
	fwrite( STDERR, implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

echo "Mall template application passed for page {$front_page_id}.\n";
