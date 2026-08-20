<?php
/**
 * Plugin Name: Admin Branding
 * Description: 后台品牌定制：Shopee 橙主题 + 优选商城 logo（登录页/菜单）+ 去 WooCommerce logo + 交互增强（本地测试）
 */

/* ========== 1. 后台主题色 + 交互样式 + 品牌 logo ========== */
add_action(
	'admin_head',
	function () {
		$logo_id = function_exists( 'shopee_get_settings' ) ? shopee_get_settings()['logo_id'] : 0;
		$logo    = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
		echo '<style>
/* ---------- WordPress 主题色变量 → Shopee 橙 ---------- */
:root {
	--wp-admin-theme-color: #ee4d2d;
	--wp-admin-theme-color--rgb: 238, 77, 45;
	--wp-admin-theme-color-darker-10: #d73211;
	--wp-admin-theme-color-darker-20: #bf2d10;
}

/* ---------- 顶部管理栏：橙色渐变 ---------- */
#wpadminbar {
	background: linear-gradient(100deg, #f53d2d 0%, #ee4d2d 60%, #febd69 140%) !important;
}
#wpadminbar .ab-sub-wrapper {
	background: #d73211 !important;
}

/* ---------- 左侧菜单 ---------- */
#adminmenu li.current a.menu-top,
#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu,
#adminmenu .wp-menu-arrow div,
#adminmenu .wp-menu-arrow {
	background: #ee4d2d !important;
	border-color: #ee4d2d !important;
}
#adminmenu .wp-has-current-submenu .wp-submenu .wp-submenu-head {
	background: #d73211 !important;
	color: #fff !important;
}
#adminmenu a {
	transition: background .18s ease, color .18s ease, padding-left .18s ease;
}
#adminmenu li.menu-top:hover > a {
	background: rgba(238, 77, 45, .12) !important;
	color: #ee4d2d !important;
}
#adminmenu .wp-submenu a:hover {
	color: #ee4d2d !important;
}
#adminmenu li.current a.menu-top:hover {
	color: #fff !important;
}

/* ---------- 按钮 ---------- */
.wp-core-ui .button-primary {
	background: #ee4d2d !important;
	border-color: #ee4d2d !important;
	transition: background .15s ease, transform .1s ease, box-shadow .15s ease;
}
.wp-core-ui .button-primary:hover,
.wp-core-ui .button-primary:focus {
	background: #d73211 !important;
	border-color: #d73211 !important;
	box-shadow: 0 2px 8px rgba(238, 77, 45, .35) !important;
}
.wp-core-ui .button-primary:active {
	transform: translateY(1px);
}

/* ---------- 链接 ---------- */
a {
	color: #ee4d2d;
	transition: color .15s ease;
}
a:hover {
	color: #d73211;
}

/* ---------- 焦点环 ---------- */
:focus,
.wp-core-ui select:focus,
.wp-core-ui input[type="text"]:focus,
.wp-core-ui input[type="email"]:focus,
.wp-core-ui input[type="password"]:focus,
.wp-core-ui textarea:focus {
	border-color: #ee4d2d !important;
	box-shadow: 0 0 0 1.5px #ee4d2d !important;
}

/* ---------- 表格行 hover ---------- */
.widefat tbody tr {
	transition: background .15s ease;
}
.widefat tbody tr:hover {
	background: #fff4f1 !important;
}

/* ---------- 卡片 hover ---------- */
.postbox,
.woocommerce-layout__header {
	transition: box-shadow .2s ease;
}
.postbox:hover {
	box-shadow: 0 4px 14px rgba(238, 77, 45, .14) !important;
}

/* ---------- 管理栏下拉 hover ---------- */
#wpadminbar .ab-top-menu > li.hover > .ab-item,
#wpadminbar.nojq .quicklinks .ab-top-menu > li > .ab-item:focus {
	background: rgba(0, 0, 0, .15) !important;
	color: #fff !important;
}

/* ---------- 去掉 WooCommerce W logo，左侧菜单显示店铺 logo 图片 ---------- */
#toplevel_page_woocommerce .wp-menu-image {
	display: none !important;
}
#toplevel_page_woocommerce .wp-menu-name {
	display: flex;
	align-items: center;
	gap: 8px;
}
#toplevel_page_woocommerce .wp-menu-name::before {
	content: "";
	display: inline-block;
	width: 22px;
	height: 22px;
	background-image: url("' . esc_url( $logo ) . '");
	background-size: contain;
	background-repeat: no-repeat;
	background-position: center;
	border-radius: 5px;
	flex-shrink: 0;
}
</style>';
	}
);

/* ========== 2. 登录页：替换默认 logo 为店铺 logo ========== */
add_action(
	'login_head',
	function () {
		$logo_id = function_exists( 'shopee_get_settings' ) ? shopee_get_settings()['logo_id'] : 0;
		$logo    = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
		if ( ! $logo ) {
			return;
		}
		echo '<style>
body.login {
	background: #f5f5f5;
}
body.login #login h1 a {
	background-image: url("' . esc_url( $logo ) . '") !important;
	background-size: contain !important;
	background-position: center !important;
	background-repeat: no-repeat !important;
	width: 120px !important;
	height: 120px !important;
	margin: 0 auto 20px !important;
	border-radius: 18px;
}
body.login #loginform {
	border-radius: 12px;
	box-shadow: 0 8px 30px rgba(0, 0, 0, .1);
}
body.login .button-primary {
	background: #ee4d2d !important;
	border-color: #ee4d2d !important;
}
</style>';
	}
);

/* ========== 4. 移除仪表盘上的所有 WooCommerce 小组件 ========== */
add_action(
	'wp_dashboard_setup',
	function () {
		$woo_widgets = array(
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
				foreach ( $woo_widgets as $id ) {
					unset( $wp_meta_boxes['dashboard'][ $context ][ $prio ][ $id ] );
				}
			}
		}
	},
	999
);

/* ========== 5. 改左侧菜单文字为店铺名 ========== */
add_action(
	'admin_footer',
	function () {
		$shop_name = function_exists( 'shopee_get_settings' ) && ! empty( shopee_get_settings()['shop_name'] ) ? shopee_get_settings()['shop_name'] : '优选商城';
		$shop_name = esc_js( $shop_name );
		echo '<script>
(function () {
	function renameMenu() {
		var el = document.querySelector("#toplevel_page_woocommerce .wp-menu-name");
		if (el && el.textContent.trim() !== "' . $shop_name . '") {
			el.textContent = "' . $shop_name . '";
		}
		var head = document.querySelector("#toplevel_page_woocommerce .wp-submenu-head");
		if (head && head.textContent.trim() !== "' . $shop_name . '") {
			head.textContent = "' . $shop_name . '";
		}
	}
	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", renameMenu);
	} else {
		renameMenu();
	}
	setTimeout(renameMenu, 500);
})();
</script>';
	}
);
