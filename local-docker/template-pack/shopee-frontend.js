/**
 * Shopee 风格前端脚本：轮播横幅 + 限时抢购倒计时 + 商品销量/评分注入
 */
(function () {
	'use strict';

	/* ---------- 1. 轮播横幅 ---------- */
	var slides = document.querySelectorAll('.shopee-slide');
	var dots = document.querySelectorAll('.shopee-dot');
	if (slides.length > 1) {
		var idx = 0;
		setInterval(function () {
			slides[idx].classList.remove('active');
			if (dots[idx]) { dots[idx].classList.remove('active'); }
			idx = (idx + 1) % slides.length;
			slides[idx].classList.add('active');
			if (dots[idx]) { dots[idx].classList.add('active'); }
		}, 4000);
		dots.forEach(function (dot, i) {
			dot.addEventListener('click', function () {
				slides[idx].classList.remove('active');
				if (dots[idx]) { dots[idx].classList.remove('active'); }
				idx = i;
				slides[idx].classList.add('active');
				if (dots[idx]) { dots[idx].classList.add('active'); }
			});
		});
	}

	/* ---------- 2. 限时抢购倒计时（到当天 23:59:59） ---------- */
	function pad(n) { return n < 10 ? '0' + n : '' + n; }
	function tick() {
		var els = document.querySelectorAll('.countdown-time');
		if (!els.length) { return; }
		var now = new Date();
		var end = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);
		var diff = Math.max(0, end.getTime() - now.getTime());
		var h = Math.floor(diff / 3600000);
		var m = Math.floor((diff % 3600000) / 60000);
		var s = Math.floor((diff % 60000) / 1000);
		var text = pad(h) + ':' + pad(m) + ':' + pad(s);
		els.forEach(function (el) { el.textContent = text; });
	}
	tick();
	setInterval(tick, 1000);

	/* ---------- 3. 商品卡片：注入销量 + 评分 ---------- */
	function injectStats() {
		if (!window.wcShopStats) { return; }
		var stats = window.wcShopStats;
		document.querySelectorAll('li.wc-block-product, li.product').forEach(function (card) {
			var m = card.className.match(/post-(\d+)/);
			if (!m) { return; }
			var data = stats[m[1]];
			if (!data) { return; }
			if (card.querySelector('.shopee-sales') || card.querySelector('.shopee-rating')) { return; }
			var price = card.querySelector('.wc-block-components-product-price') || card.querySelector('.price');
			if (!price) { return; }
			var frag = document.createElement('div');
			frag.style.cssText = 'padding:2px 12px 12px;line-height:1.7;';
			var rating = document.createElement('div');
			rating.className = 'shopee-rating';
			var full = Math.round(data.rating) || 0;
			var stars = '';
			for (var i = 0; i < full; i++) { stars += '★'; }
			for (var j = full; j < 5; j++) { stars += '☆'; }
			rating.innerHTML = '<span class="star">' + stars + '</span> ' + (data.rating ? data.rating.toFixed(1) : '暂无评分');
			var sales = document.createElement('div');
			sales.className = 'shopee-sales';
			sales.textContent = '已售 ' + data.sales + ' 件';
			frag.appendChild(rating);
			frag.appendChild(sales);
			price.parentNode.insertBefore(frag, price.nextSibling);
		});
	}
	injectStats();
	setTimeout(injectStats, 1500); // 等 blocks hydration 后兜底再注入

	/* ---------- 4. 主题切换器 ---------- */
	function initThemeSwitcher() {
		var toggle = document.querySelector('.shopee-theme-toggle');
		var menu = document.querySelector('.shopee-theme-menu');
		if (!toggle || !menu) { return; }
		toggle.addEventListener('click', function (e) {
			e.stopPropagation();
			menu.classList.toggle('open');
		});
		document.addEventListener('click', function () {
			menu.classList.remove('open');
		});
		menu.addEventListener('click', function (e) {
			var btn = e.target.closest('button[data-theme]');
			if (!btn) { return; }
			var theme = btn.getAttribute('data-theme');
			document.documentElement.setAttribute('data-shopee-theme', theme);
			try { localStorage.setItem('shopeeTheme', theme); } catch (err) { /* ignore */ }
			menu.querySelectorAll('button').forEach(function (b) {
				b.classList.toggle('active', b === btn);
			});
			menu.classList.remove('open');
		});
	}
	initThemeSwitcher();
})();
