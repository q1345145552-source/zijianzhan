import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

const root = new URL('../', import.meta.url);
const sourceHtml = readFileSync(new URL('home-page.html', root), 'utf8');
const packedHtml = readFileSync(new URL('template-pack/home-page.html', root), 'utf8');
const sourceCss = readFileSync(new URL('shopee-style.css', root), 'utf8');
const packedCss = readFileSync(new URL('template-pack/shopee-style.css', root), 'utf8');
const sourceJs = readFileSync(new URL('shopee-frontend.js', root), 'utf8');
const packedJs = readFileSync(new URL('template-pack/shopee-frontend.js', root), 'utf8');
const sourceHeader = readFileSync(new URL('header.php', root), 'utf8');
const packedHeader = readFileSync(new URL('template-pack/header.php', root), 'utf8');
const sourceLoader = readFileSync(new URL('shopee-style-loader.php', root), 'utf8');
const packedLoader = readFileSync(new URL('template-pack/shopee-style-loader.php', root), 'utf8');

const requiredSections = [
	'mall-shop-hero',
	'mall-shop-actions',
	'mall-shop-tabs',
	'mall-announcement',
	'mall-voucher-strip',
	'mall-category-rail',
	'mall-product-section',
	'mall-mobile-nav',
];

for (const section of requiredSections) {
	assert.match(sourceHtml, new RegExp(`\\b${section}\\b`), `missing ${section}`);
	assert.match(sourceCss, new RegExp(`\\.${section}\\b`), `missing styles for ${section}`);
}

assert.match(sourceHtml, /lang="th"/, 'Mall template must identify Thai content');
assert.doesNotMatch(sourceHtml, /\p{Script=Han}/u, 'Mall template must not contain Chinese text');
assert.match(sourceHtml, /<div[^>]+class="[^"]*mall-product-section/, 'product section needs a rendered wrapper');
assert.match(sourceCss, /@media \(max-width: 767px\)/, 'mobile Mall breakpoint is missing');
assert.match(sourceJs, /ยังไม่มีคะแนน/, 'empty rating label must be Thai');
assert.match(sourceJs, /ขายแล้ว/, 'sales label must be Thai');
assert.match(sourceHeader, /ค้นหาสินค้า/, 'header search placeholder must be Thai');
assert.doesNotMatch(sourceLoader, /shopee-theme-switcher|shopee-theme-toggle/, 'visitor theme switcher must be absent');
assert.equal(sourceHtml, packedHtml, 'template-pack HTML must match the working copy');
assert.equal(sourceCss, packedCss, 'template-pack CSS must match the working copy');
assert.equal(sourceJs, packedJs, 'template-pack JavaScript must match the working copy');
assert.equal(sourceHeader, packedHeader, 'template-pack header must match the working copy');
assert.equal(sourceLoader, packedLoader, 'template-pack loader must match the working copy');

console.log('Mobile Mall template contract passed.');
