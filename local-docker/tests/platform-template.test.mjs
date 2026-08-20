import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const root = new URL('../', import.meta.url);
const admin = readFileSync(new URL('shopee-admin-panel.php', root), 'utf8');
const loader = readFileSync(new URL('shopee-style-loader.php', root), 'utf8');
const frontend = readFileSync(new URL('shopee-frontend.js', root), 'utf8');
const tiktokCss = readFileSync(new URL('tiktok-style.css', root), 'utf8');
const tiktokJs = readFileSync(new URL('tiktok-frontend.js', root), 'utf8');
const lazadaCss = readFileSync(new URL('lazada-style.css', root), 'utf8');
const lazadaJs = readFileSync(new URL('lazada-frontend.js', root), 'utf8');

assert.match(admin, /'platform'\s*=>\s*'shopee'/, 'Shopee must be the default platform');
assert.match(admin, /id="shopee-platform"/, 'platform selector is missing');
assert.match(admin, /value="shopee"/, 'Shopee platform option is missing');
assert.match(admin, /value="tiktok"/, 'TikTok platform option is missing');
assert.match(admin, /value="lazada"/, 'Lazada platform option is missing');
assert.match(admin, /id="shopee-theme-row"/, 'Shopee colour row needs a toggle target');
assert.match(admin, /toggleShopeeThemes/, 'platform-dependent colour visibility is missing');
assert.match(admin, /in_array\(\s*\$input\['platform'\][\s\S]*?'shopee'[\s\S]*?'tiktok'[\s\S]*?'lazada'/, 'platform sanitization is missing');

assert.match(loader, /data-store-platform/, 'the saved platform is not applied before rendering');
assert.match(loader, /tiktok-style\.css/, 'TikTok stylesheet is not conditionally loaded');
assert.match(loader, /tiktok-frontend\.js/, 'TikTok script is not conditionally loaded');
assert.match(loader, /lazada-style\.css/, 'Lazada stylesheet is not conditionally loaded');
assert.match(loader, /lazada-frontend\.js/, 'Lazada script is not conditionally loaded');
assert.doesNotMatch(loader, /shopee-theme-switcher|shopee-theme-toggle/, 'visitor theme switcher must not be rendered');
assert.doesNotMatch(frontend, /initThemeSwitcher|localStorage|data-theme/, 'visitor theme switching code must be removed');

for (const selector of [
	'data-store-platform="tiktok"',
	'.single-product',
	'.woocommerce-shop',
	'.wc-block-cart',
	'.wc-block-checkout',
	'.mall-campaign-carousel',
	'li.product',
]) {
	assert.ok(tiktokCss.includes(selector), `TikTok styles must cover ${selector}`);
}

assert.match(tiktokJs, /data-store-platform/, 'TikTok script must guard itself by platform');
assert.match(tiktokJs, /IntersectionObserver/, 'TikTok product wall needs lightweight reveal motion');

for (const selector of [
	'data-store-platform="lazada"',
	'.single-product',
	'.woocommerce-shop',
	'.wc-block-cart',
	'.wc-block-checkout',
	'.mall-campaign-carousel',
	'li.product',
]) {
	assert.ok(lazadaCss.includes(selector), `Lazada styles must cover ${selector}`);
}

assert.match(lazadaJs, /data-store-platform/, 'Lazada script must guard itself by platform');
assert.match(lazadaJs, /IntersectionObserver/, 'Lazada product wall needs lightweight reveal motion');

for (const file of [
	'shopee-admin-panel.php',
	'shopee-style-loader.php',
	'shopee-frontend.js',
	'tiktok-style.css',
	'tiktok-frontend.js',
	'lazada-style.css',
	'lazada-frontend.js',
]) {
	const source = readFileSync(new URL(file, root), 'utf8');
	const packed = readFileSync(new URL(`template-pack/${file}`, root), 'utf8');
	assert.equal(source, packed, `template-pack/${file} must match the working copy`);
}

console.log('Platform template contract passed.');
