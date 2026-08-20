<?php
/**
 * Plugin Name: Shopee Admin Panel
 * Description: 店铺装修后台面板：主题色 / 店铺名 / 副标题 / Logo / 功能开关（本地测试）
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- 默认设置 ---------- */
function shopee_panel_defaults() {
	return array(
		'theme'         => 'default', // default | minimal | black-gold | green
		'shop_name'     => '',
		'shop_tagline'  => '',
		'logo_id'       => 0,
		'show_stats'    => 1, // 商品销量/评分
		'show_switcher' => 1, // 右下角主题切换器
	);
}

/* ---------- 读取设置（供其他 mu-plugin 使用） ---------- */
function shopee_get_settings() {
	$defaults = shopee_panel_defaults();
	return wp_parse_args( get_option( 'shopee_settings', array() ), $defaults );
}

/* ---------- 后台菜单：WooCommerce 下加「店铺装修」 ---------- */
add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'woocommerce',
			'店铺装修',
			'店铺装修',
			'manage_options',
			'shopee-panel',
			'shopee_panel_render'
		);
	},
	99
);

/* ---------- 注册设置 ---------- */
add_action(
	'admin_init',
	function () {
		register_setting( 'shopee_settings_group', 'shopee_settings', 'shopee_settings_sanitize' );
	}
);

/* ---------- 数据清洗 + 同步 ---------- */
function shopee_settings_sanitize( $input ) {
	$defaults = shopee_panel_defaults();
	$clean    = array(
		'theme'         => isset( $input['theme'] ) && in_array( $input['theme'], array( 'default', 'minimal', 'black-gold', 'green' ), true ) ? $input['theme'] : 'default',
		'shop_name'     => isset( $input['shop_name'] ) ? sanitize_text_field( $input['shop_name'] ) : '',
		'shop_tagline'  => isset( $input['shop_tagline'] ) ? sanitize_text_field( $input['shop_tagline'] ) : '',
		'logo_id'       => isset( $input['logo_id'] ) ? absint( $input['logo_id'] ) : 0,
		'show_stats'    => empty( $input['show_stats'] ) ? 0 : 1,
		'show_switcher' => empty( $input['show_switcher'] ) ? 0 : 1,
	);
	// 同步 logo 到站点图标与站点 logo
	if ( $clean['logo_id'] ) {
		update_option( 'site_icon', $clean['logo_id'] );
		set_theme_mod( 'custom_logo', $clean['logo_id'] );
	}
	return $clean;
}

/* ---------- 渲染面板 ---------- */
function shopee_panel_render() {
	$s        = shopee_get_settings();
	$logo_url = $s['logo_id'] ? wp_get_attachment_image_url( $s['logo_id'], 'thumbnail' ) : '';
	$themes   = array(
		'default'    => 'Shopee 橙（默认）',
		'minimal'    => '简约白',
		'black-gold' => '黑金奢华',
		'green'      => '清新绿',
	);
	?>
	<div class="wrap">
		<h1>店铺装修面板</h1>
		<p>在这里可以可视化地调整店铺的前台外观，保存后立即生效（前台与后台同步）。</p>

		<?php if ( isset( $_GET['settings-updated'] ) ) : // phpcs:ignore ?>
			<div class="notice notice-success is-dismissible"><p>装修设置已保存！</p></div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'shopee_settings_group' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="shopee-theme">默认主题色</label></th>
					<td>
						<select name="shopee_settings[theme]" id="shopee-theme" style="min-width:220px;">
							<?php foreach ( $themes as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $s['theme'], $key ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description">前台默认配色。顾客仍可随时用右下角「主题」按钮切换（若开启该功能）。</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="shopee-name">店铺名称</label></th>
					<td>
						<input type="text" name="shopee_settings[shop_name]" id="shopee-name" class="regular-text" value="<?php echo esc_attr( $s['shop_name'] ); ?>" placeholder="优选商城">
						<p class="description">显示在网站顶部和浏览器标题。留空则用 WordPress 站点名称。</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="shopee-tagline">店铺副标题</label></th>
					<td>
						<input type="text" name="shopee_settings[shop_tagline]" id="shopee-tagline" class="regular-text" value="<?php echo esc_attr( $s['shop_tagline'] ); ?>" placeholder="泰国优选好物，一站购齐">
						<p class="description">显示在网站副标题位置。留空则用 WordPress 副标题。</p>
					</td>
				</tr>
				<tr>
					<th scope="row">店铺 Logo</th>
					<td>
						<input type="hidden" name="shopee_settings[logo_id]" id="shopee-logo-id" value="<?php echo esc_attr( $s['logo_id'] ); ?>">
						<img id="shopee-logo-preview" src="<?php echo esc_url( $logo_url ); ?>" alt="" style="max-width:120px;max-height:120px;border-radius:12px;<?php echo $logo_url ? '' : 'display:none;'; ?>margin-bottom:8px;border:1px solid #ddd;">
						<br>
						<button type="button" class="button" id="shopee-logo-upload">选择图片</button>
						<button type="button" class="button" id="shopee-logo-remove">移除</button>
						<p class="description">用于前台顶部、后台菜单、登录页、浏览器图标。推荐正方形透明 PNG。</p>
					</td>
				</tr>
				<tr>
					<th scope="row">功能开关</th>
					<td>
						<label>
							<input type="checkbox" name="shopee_settings[show_stats]" value="1" <?php checked( $s['show_stats'], 1 ); ?>>
							显示商品销量与评分
						</label>
						<br>
						<label>
							<input type="checkbox" name="shopee_settings[show_switcher]" value="1" <?php checked( $s['show_switcher'], 1 ); ?>>
							显示右下角主题切换按钮
						</label>
						<p class="description">取消勾选后对应功能立即隐藏。</p>
					</td>
				</tr>
			</table>
			<?php submit_button( '保存装修设置' ); ?>
		</form>

		<hr>
		<h2>当前装修信息</h2>
		<table class="widefat striped" style="max-width:560px;">
			<tr><th>模板版本</th><td>Shopee 装修模板 v1.0.4（前台 4 套主题 + 购物车 + 后台橙色）</td></tr>
			<tr><th>前台样式文件</th><td>wp-content/mu-plugins/shopee-style.css</td></tr>
			<tr><th>后台样式文件</th><td>wp-content/mu-plugins/admin-branding.php</td></tr>
			<tr><th>模板包备份</th><td>local-docker/template-pack/（含部署说明 README）</td></tr>
		</table>
	</div>

	<script>
	jQuery( function ( $ ) {
		var frame;
		$( '#shopee-logo-upload' ).on( 'click', function ( e ) {
			e.preventDefault();
			if ( frame ) { frame.open(); return; }
			frame = wp.media( {
				title: '选择店铺 Logo',
				button: { text: '使用此图片' },
				multiple: false
			} );
			frame.on( 'select', function () {
				var att = frame.state().get( 'selection' ).first().toJSON();
				$( '#shopee-logo-id' ).val( att.id );
				$( '#shopee-logo-preview' ).attr( 'src', att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url ).show();
			} );
			frame.open();
		} );
		$( '#shopee-logo-remove' ).on( 'click', function ( e ) {
			e.preventDefault();
			$( '#shopee-logo-id' ).val( 0 );
			$( '#shopee-logo-preview' ).hide();
		} );
	} );
	</script>
	<?php
}

/* ---------- 面板页加载媒体库 JS ---------- */
add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( false !== strpos( $hook, 'shopee-panel' ) ) {
			wp_enqueue_media();
		}
	}
);
