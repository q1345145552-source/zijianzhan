<?php
/**
 * Plugin Name: Force Locale
 * Description: 前台显示泰语（th），后台显示简体中文（zh_CN）
 */

add_filter( 'locale', 'zijianzhan_force_locale' );
add_filter( 'determine_locale', 'zijianzhan_force_locale' );

function zijianzhan_force_locale( $locale ) {
	// 后台（wp-admin）用中文，前台用泰语
	if ( is_admin() ) {
		return 'zh_CN';
	}
	return 'th';
}

/* 兜底：前台界面文案强制泰语（解决个别 blocks 文案未翻译） */
add_filter(
	'gettext',
	function ( $translated, $text, $domain ) {
		if ( is_admin() ) {
			return $translated;
		}
		// 空购物车提示
		if ( 'Your cart is currently empty!' === $text ) {
			return 'ขณะนี้ตะกร้าสินค้าของคุณว่างเปล่า';
		}
		// 注册表单隐私说明（含中文残留则替换）
		if ( false !== strpos( $translated, '其他用途' ) ) {
			$translated = str_replace( '其他用途', 'วัตถุประสงค์อื่น ๆ ตามที่อธิบายไว้ใน', $translated );
			$translated = str_replace( '您的个人资料将用于在您体验本网站的整个过程中为您提供支持、管理对您帐户的访问，以及用于在我们的', 'ข้อมูลส่วนบุคคลของคุณจะถูกนำไปใช้เพื่อสนับสนุนประสบการณ์การใช้งานเว็บไซต์ จัดการการเข้าถึงบัญชีของคุณ และใช้เพื่อ', $translated );
		}
		return $translated;
	},
	20,
	3
);

/* 前端 JS 兜底：替换残留中文界面文字为泰语 */
add_action(
	'wp_footer',
	function () {
		if ( is_admin() ) {
			return;
		}
		echo '<script>
(function () {
	function fix() {
		/* 空购物车标题 */
		document.querySelectorAll(".wc-block-cart__empty-cart__title").forEach(function (el) {
			if (el.textContent.indexOf("ขณะนี้ตะกร้าสินค้าของคุณว่างเปล่า") === -1) {
				el.textContent = "ขณะนี้ตะกร้าสินค้าของคุณว่างเปล่า";
			}
		});
	}
	fix();
	/* MutationObserver：blocks 重新渲染后再次替换 */
	var mo = new MutationObserver(fix);
	mo.observe(document.body, { childList: true, subtree: true });
	/* 通用文本替换（含中文残留的界面文字） */
	var repl = {
		"中描述的其他用途。": "ตามวัตถุประสงค์อื่น ๆ ตามที่อธิบายไว้ในนโยบายความเป็นส่วนตัว",
		"您的购物车目前是空的！": "ขณะนี้ตะกร้าสินค้าของคุณว่างเปล่า"
	};
	var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
	var nodes = [];
	var n;
	while ((n = walker.nextNode())) { nodes.push(n); }
	nodes.forEach(function (node) {
		for (var k in repl) {
			if (node.nodeValue.indexOf(k) !== -1) {
				node.nodeValue = node.nodeValue.split(k).join(repl[k]);
			}
		}
	});
})();
</script>';
	},
	999
);
