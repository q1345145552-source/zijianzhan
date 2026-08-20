<?php
/**
 * Plugin Name: Shopee Style Loader
 * Description: 加载 Shopee 泰国站风格定制样式与脚本；读取「店铺装修面板」设置（本地测试）
 */

/* ---------- 移除 WooCommerce 生成器标签 ---------- */
add_action(
	'plugins_loaded',
	function () {
		remove_filter( 'get_the_generator_html', 'wc_generator_tag', 10 );
		remove_filter( 'get_the_generator_xhtml', 'wc_generator_tag', 10 );
	}
);

/* ---------- 加载样式与脚本 ---------- */
add_action(
	'wp_enqueue_scripts',
	function () {
		$settings = function_exists( 'shopee_get_settings' ) ? shopee_get_settings() : array( 'platform' => 'shopee' );
		$platform = isset( $settings['platform'] ) && in_array( $settings['platform'], array( 'shopee', 'tiktok', 'lazada' ), true ) ? $settings['platform'] : 'shopee';

		wp_enqueue_style( 'shopee-style', content_url() . '/mu-plugins/shopee-style.css', array(), '1.3.0' );
		wp_enqueue_script( 'shopee-frontend', content_url() . '/mu-plugins/shopee-frontend.js', array(), '1.3.0', true );

		if ( 'tiktok' === $platform ) {
			wp_enqueue_style( 'tiktok-style', content_url() . '/mu-plugins/tiktok-style.css', array( 'shopee-style' ), '1.0.0' );
			wp_enqueue_script( 'tiktok-frontend', content_url() . '/mu-plugins/tiktok-frontend.js', array(), '1.0.0', true );
		}

		if ( 'lazada' === $platform ) {
			wp_enqueue_style( 'lazada-style', content_url() . '/mu-plugins/lazada-style.css', array( 'shopee-style' ), '1.0.0' );
			wp_enqueue_script( 'lazada-frontend', content_url() . '/mu-plugins/lazada-frontend.js', array(), '1.0.0', true );
		}
	}
);

/* ---------- 店铺名 / 副标题（来自装修面板，留空用 WordPress 默认） ---------- */
add_filter(
	'option_blogname',
	function ( $value ) {
		if ( function_exists( 'shopee_get_settings' ) ) {
			$s = shopee_get_settings();
			if ( ! empty( $s['shop_name'] ) ) {
				return $s['shop_name'];
			}
		}
		return $value;
	}
);
add_filter(
	'option_blogdescription',
	function ( $value ) {
		if ( function_exists( 'shopee_get_settings' ) ) {
			$s = shopee_get_settings();
			if ( ! empty( $s['shop_tagline'] ) ) {
				return $s['shop_tagline'];
			}
		}
		return $value;
	}
);

/* ---------- 页面渲染前应用店长保存的平台与 Shopee 配色 ---------- */
add_action(
	'wp_head',
	function () {
		$settings = function_exists( 'shopee_get_settings' ) ? shopee_get_settings() : array( 'platform' => 'shopee', 'theme' => 'default' );
		$platform = isset( $settings['platform'] ) && in_array( $settings['platform'], array( 'shopee', 'tiktok', 'lazada' ), true ) ? $settings['platform'] : 'shopee';
		$theme    = isset( $settings['theme'] ) ? $settings['theme'] : 'default';

		echo '<script>(function(){var r=document.documentElement;r.setAttribute("data-store-platform",' . wp_json_encode( $platform ) . ');r.setAttribute("data-shopee-theme",' . wp_json_encode( $theme ) . ');})();</script>';
	},
	1
);

/* ---------- 商品销量/评分数据（按面板开关） ---------- */
add_action(
	'wp_footer',
	function () {
		if ( function_exists( 'shopee_get_settings' ) && empty( shopee_get_settings()['show_stats'] ) ) {
			return;
		}
		$stats = array();
		$query = new WP_Query(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 100,
				'post_status'    => 'publish',
			)
		);
		foreach ( $query->posts as $post ) {
			$sales  = (int) get_post_meta( $post->ID, 'total_sales', true );
			$rating = 0;
			$count  = 0;
			foreach ( get_comments( array( 'post_id' => $post->ID, 'status' => 'approve', 'type' => 'review' ) ) as $c ) {
				$r = (int) get_comment_meta( $c->comment_ID, 'rating', true );
				if ( $r ) {
					$rating += $r;
					$count++;
				}
			}
			$stats[ $post->ID ] = array(
				'sales'  => $sales,
				'rating' => $count ? round( $rating / $count, 1 ) : 0,
			);
		}
		echo '<script>window.wcShopStats = ' . wp_json_encode( $stats ) . ';</script>';
	},
	10
);
