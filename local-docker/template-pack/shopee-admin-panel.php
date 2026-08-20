<?php
/**
 * Plugin Name: Shopee Admin Panel
 * Description: 店铺装修后台面板：平台模板 / 主题色 / 店铺名 / 副标题 / Logo / 功能开关（本地测试）
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- 默认设置 ---------- */
function shopee_panel_defaults() {
	return array(
		'platform'      => 'shopee', // shopee | tiktok | lazada
		'theme'         => 'default', // default | minimal | black-gold | green
		'shop_name'     => '',
		'shop_tagline'  => '',
		'logo_id'       => 0,
		'show_stats'    => 1, // 商品销量/评分
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
	$clean = array(
		'platform'     => isset( $input['platform'] ) && in_array( $input['platform'], array( 'shopee', 'tiktok', 'lazada' ), true ) ? $input['platform'] : 'shopee',
		'theme'        => isset( $input['theme'] ) && in_array( $input['theme'], array( 'default', 'minimal', 'black-gold', 'green' ), true ) ? $input['theme'] : 'default',
		'shop_name'    => isset( $input['shop_name'] ) ? sanitize_text_field( $input['shop_name'] ) : '',
		'shop_tagline' => isset( $input['shop_tagline'] ) ? sanitize_text_field( $input['shop_tagline'] ) : '',
		'logo_id'      => isset( $input['logo_id'] ) ? absint( $input['logo_id'] ) : 0,
		'show_stats'   => empty( $input['show_stats'] ) ? 0 : 1,
	);
	// 同步 logo 到站点图标与站点 logo
	if ( $clean['logo_id'] ) {
		update_option( 'site_icon', $clean['logo_id'] );
		set_theme_mod( 'custom_logo', $clean['logo_id'] );
	}
	return $clean;
}

/* ---------- Shopee Mall 商品模板应用器 ---------- */
final class Shopee_Mall_Template_Applier {
	/**
	 * 将随插件部署的 Mall 首页模板应用为站点首页。
	 *
	 * @return int|WP_Error 首页页面 ID，失败时返回错误对象。
	 */
	public static function apply() {
		$template_file = __DIR__ . '/home-page.html';

		if ( ! is_readable( $template_file ) ) {
			return new WP_Error( 'mall_template_missing', '找不到商品模板文件。' );
		}

		$template_content = file_get_contents( $template_file );
		if ( false === $template_content || false === strpos( $template_content, 'mall-shop-hero' ) ) {
			return new WP_Error( 'mall_template_invalid', '商品模板文件内容无效。' );
		}

		$front_page_id = absint( get_option( 'page_on_front' ) );
		$page_data     = array(
			'post_title'   => 'หน้าหลัก',
			'post_content' => $template_content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
		);

		if ( $front_page_id && 'page' === get_post_type( $front_page_id ) ) {
			$page_data['ID'] = $front_page_id;
			$result          = wp_update_post( $page_data, true );
		} else {
			$result = wp_insert_post( $page_data, true );
		}

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$front_page_id = (int) $result;
		$settings      = wp_parse_args( get_option( 'shopee_settings', array() ), shopee_panel_defaults() );
		$settings      = array_merge(
			$settings,
			array(
				'platform'     => 'shopee',
				'theme'        => 'default',
				'shop_name'    => 'เซียงไท่ มอลล์',
				'shop_tagline' => 'สินค้าคัดสรรคุณภาพ ครบจบในที่เดียว',
				'show_stats'   => 1,
			)
		);

		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front_page_id );
		update_option( 'blogname', 'เซียงไท่ มอลล์' );
		update_option( 'blogdescription', 'สินค้าคัดสรรคุณภาพ ครบจบในที่เดียว' );
		update_option( 'WPLANG', 'th' );
		update_option( 'shopee_settings', $settings );

		return $front_page_id;
	}

	/**
	 * 处理后台“应用商品模板”请求。
	 */
	public static function handle_request() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( '你没有权限应用商品模板。' );
		}

		check_admin_referer( 'shopee_apply_mall_template' );
		$result       = self::apply();
		$redirect_url = admin_url( 'admin.php?page=shopee-panel' );

		if ( is_wp_error( $result ) ) {
			$redirect_url = add_query_arg( 'template-error', sanitize_key( $result->get_error_code() ), $redirect_url );
		} else {
			$redirect_url = add_query_arg( 'template-applied', '1', $redirect_url );
		}

		wp_safe_redirect( $redirect_url );
		exit;
	}
}

add_action( 'admin_post_shopee_apply_mall_template', array( 'Shopee_Mall_Template_Applier', 'handle_request' ) );

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
		<?php if ( isset( $_GET['template-applied'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['template-applied'] ) ) ) : ?>
			<div class="notice notice-success is-dismissible"><p>商品模板已应用，前台已切换为 Shopee Mall 手机店布局。</p></div>
		<?php endif; ?>
		<?php if ( isset( $_GET['template-error'] ) ) : ?>
			<div class="notice notice-error is-dismissible"><p>商品模板应用失败，请确认模板文件已部署。</p></div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'shopee_settings_group' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="shopee-platform">平台模板</label></th>
					<td>
						<select name="shopee_settings[platform]" id="shopee-platform" style="min-width:220px;">
							<option value="shopee" <?php selected( $s['platform'], 'shopee' ); ?>>Shopee</option>
							<option value="tiktok" <?php selected( $s['platform'], 'tiktok' ); ?>>TikTok</option>
							<option value="lazada" <?php selected( $s['platform'], 'lazada' ); ?>>Lazada</option>
						</select>
						<p class="description">保存后全店统一使用所选平台外观，访客无法自行切换。</p>
					</td>
				</tr>
				<tr id="shopee-theme-row">
					<th scope="row"><label for="shopee-theme">默认主题色</label></th>
					<td>
						<select name="shopee_settings[theme]" id="shopee-theme" style="min-width:220px;">
							<?php foreach ( $themes as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $s['theme'], $key ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description">仅用于 Shopee 平台模板。</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="shopee-name">店铺名称</label></th>
					<td>
						<input type="text" name="shopee_settings[shop_name]" id="shopee-name" class="regular-text" value="<?php echo esc_attr( $s['shop_name'] ); ?>" placeholder="湘泰商城">
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
						<p class="description">取消勾选后对应功能立即隐藏。</p>
					</td>
				</tr>
			</table>
			<?php submit_button( '保存装修设置' ); ?>
		</form>

		<div style="max-width:760px;margin:24px 0;padding:20px;background:#fff;border:1px solid #dcdcde;border-left:4px solid #d0011b;box-shadow:0 1px 2px rgba(0,0,0,.04);">
			<h2 style="margin-top:0;">商品模板</h2>
			<p><strong>Shopee Mall 手机店模板</strong></p>
			<p>包含 Mall 红色顶栏、店铺封面、导航、公告、活动轮播、权益卡、圆形分类、双列商品流和手机底部导航。点击后会同时应用泰语店名、泰语首页和默认配色。</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="shopee_apply_mall_template">
				<?php wp_nonce_field( 'shopee_apply_mall_template' ); ?>
				<?php submit_button( '应用商品模板', 'primary', 'submit', false ); ?>
				<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener noreferrer">预览当前首页</a>
			</form>
		</div>

		<hr>
		<h2>当前装修信息</h2>
		<table class="widefat striped" style="max-width:560px;">
			<tr><th>模板版本</th><td>平台模板 v1.3.0（Shopee + TikTok + Lazada）</td></tr>
			<tr><th>前台样式文件</th><td>shopee-style.css / tiktok-style.css / lazada-style.css</td></tr>
			<tr><th>后台样式文件</th><td>wp-content/mu-plugins/admin-branding.php</td></tr>
			<tr><th>模板包备份</th><td>local-docker/template-pack/（含部署说明 README）</td></tr>
		</table>
	</div>

	<script>
	jQuery( function ( $ ) {
		var frame;
		function toggleShopeeThemes() {
			$( '#shopee-theme-row' ).toggle( 'shopee' === $( '#shopee-platform' ).val() );
		}
		$( '#shopee-platform' ).on( 'change', toggleShopeeThemes );
		toggleShopeeThemes();

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
