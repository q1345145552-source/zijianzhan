<?php
/**
 * Plugin Name: Force Chinese
 * Description: 强制站点/后台语言为简体中文
 */

add_filter( 'locale', 'zijianzhan_force_zh_cn' );
add_filter( 'determine_locale', 'zijianzhan_force_zh_cn' );

function zijianzhan_force_zh_cn( $locale ) {
	return 'zh_CN';
}
