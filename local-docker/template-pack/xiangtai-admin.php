<?php
/**
 * Plugin Name: Xiangtai Admin Branding
 * Description: 湘泰商城后台品牌化：去除所有 WooCommerce 痕迹，统一显示为「湘泰商城」
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* 品牌名与屏蔽词 */
function xt_brand() {
	return '湘泰商城';
}
function xt_hidden_words() {
	return array( 'WooCommerce', 'Automattic' );
}

/* ========== 1. 全局文字替换（后台界面） ========== */
add_filter(
	'gettext',
	function ( $translated, $text, $domain ) {
		if ( is_admin() || wp_doing_ajax() ) {
			foreach ( xt_hidden_words() as $word ) {
				$translated = str_replace( $word, xt_brand(), $translated );
			}
		}
		return $translated;
	},
	10,
	3
);
add_filter(
	'gettext_with_context',
	function ( $translated, $text, $context, $domain ) {
		if ( is_admin() || wp_doing_ajax() ) {
			foreach ( xt_hidden_words() as $word ) {
				$translated = str_replace( $word, xt_brand(), $translated );
			}
		}
		return $translated;
	},
	10,
	4
);
add_filter(
	'ngettext',
	function ( $translated, $single, $plural, $number, $domain ) {
		if ( is_admin() || wp_doing_ajax() ) {
			foreach ( xt_hidden_words() as $word ) {
				$translated = str_replace( $word, xt_brand(), $translated );
			}
		}
		return $translated;
	},
	10,
	5
);

/* ========== 2. 插件列表：隐藏插件元数据 ========== */
add_filter(
	'all_plugins',
	function ( $plugins ) {
		foreach ( $plugins as $file => $data ) {
			if ( isset( $data['Name'] ) && 'WooCommerce' === $data['Name'] ) {
				$plugins[ $file ]['Name']        = xt_brand();
				$plugins[ $file ]['Description'] = xt_brand() . ' 电商系统 — 商品、订单、支付一站式管理平台。';
				$plugins[ $file ]['Author']      = xt_brand() . '团队';
				$plugins[ $file ]['AuthorURI']   = 'https://xiangtai.fun';
				$plugins[ $file ]['PluginURI']   = 'https://xiangtai.fun';
			}
		}
		return $plugins;
	}
);

/* ========== 3. 左侧菜单：改名为湘泰商城 ========== */
add_action(
	'admin_menu',
	function () {
		global $menu;
		foreach ( $menu as $key => $item ) {
			if ( isset( $item[2] ) && ( 'wc-admin' === $item[2] || 'toplevel_page_woocommerce' === $item[2] ) ) {
				$menu[ $key ][0] = xt_brand();
			}
		}
	},
	999
);

/* ========== 4. 移除仪表盘上的品牌小组件 ========== */
add_action(
	'wp_dashboard_setup',
	function () {
		$widgets = array(
			'wc_admin_dashboard_setup',
			'woocommerce_dashboard_status',
			'woocommerce_dashboard_recent_reviews',
			'woocommerce_dashboard_activity',
		);
		global $wp_meta_boxes;
		if ( empty( $wp_meta_boxes['dashboard'] ) || ! is_array( $wp_meta_boxes['dashboard'] ) ) {
			return;
		}
		foreach ( $wp_meta_boxes['dashboard'] as $context => $priorities ) {
			if ( ! is_array( $priorities ) ) {
				continue;
			}
			foreach ( $priorities as $prio => $boxes ) {
				if ( ! is_array( $boxes ) ) {
					continue;
				}
				foreach ( $widgets as $id ) {
					unset( $wp_meta_boxes['dashboard'][ $context ][ $prio ][ $id ] );
				}
			}
		}
	},
	999
);

/* ========== 5. 后台 CSS：隐藏剩余品牌元素 ========== */
add_action(
	'admin_head',
	function () {
		echo '<style>
#toplevel_page_woocommerce .wp-menu-image { display: none !important; }
.woocommerce-layout__header .woocommerce-layout__header-wrapper .components-button:first-child,
a[href*="woocommerce.com"],
a[href*="wordpress.org/support/plugin/woocommerce"] { display: none !important; }
#footer-upgrade { display: none !important; }
</style>';
	}
);


/* ========== 7. 页面标题后缀处理 ========== */
add_filter(
	'admin_title',
	function ( $admin_title ) {
		foreach ( xt_hidden_words() as $word ) {
			$admin_title = str_replace( $word, xt_brand(), $admin_title );
		}
		return $admin_title;
	}
);

/* ========== 6. 后台 JS：兜底替换 JS 渲染后的残留文本 ========== */
add_action(
	'admin_footer',
	function () {
		$word_a = 'Woo' . 'Commerce';
		$word_b = 'Auto' . 'mattic';
		$brand  = xt_brand();
		echo '<script>
(function () {
	var words = [' . wp_json_encode( $word_a ) . ', ' . wp_json_encode( $word_b ) . '];
	var brand = ' . wp_json_encode( $brand ) . ';
	function replaceAll(str) {
		words.forEach(function (w) { str = str.split(w).join(brand); });
		return str;
	}
	function replaceText() {
		var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
			acceptNode: function (node) {
				for (var i = 0; i < words.length; i++) {
					if (node.nodeValue.indexOf(words[i]) !== -1) { return NodeFilter.FILTER_ACCEPT; }
				}
				return NodeFilter.FILTER_REJECT;
			}
		});
		var nodes = [];
		var n;
		while ((n = walker.nextNode())) { nodes.push(n); }
		nodes.forEach(function (node) { node.nodeValue = replaceAll(node.nodeValue); });
		document.querySelectorAll("[title], [aria-label]").forEach(function (el) {
			if (el.title) { el.title = replaceAll(el.title); }
			if (el.getAttribute && el.getAttribute("aria-label")) { el.setAttribute("aria-label", replaceAll(el.getAttribute("aria-label"))); }
		});
	}
	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", replaceText);
	} else {
		replaceText();
	}
	setTimeout(replaceText, 500);
	setTimeout(replaceText, 2000);
})();
</script>';
	}
);
