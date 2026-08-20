/**
 * Lazada 商城视觉增强：添加促销标签和轻量入场效果，不修改 WooCommerce 行为。
 */
(function () {
	'use strict';

	if ('lazada' !== document.documentElement.getAttribute('data-store-platform')) {
		return;
	}

	document.documentElement.classList.add('lazada-store-ready');

	var badge = document.querySelector('.mall-shop-badge');
	if (badge) {
		badge.textContent = 'LAZADA MALL · OFFICIAL';
	}

	document.querySelectorAll('.mall-campaign-label').forEach(function (label, index) {
		label.textContent = 0 === index ? 'MEGA DEALS' : 'LAZADA PICKS';
	});

	var cards = document.querySelectorAll('li.product, .wc-block-product');
	cards.forEach(function (card) {
		var title = card.querySelector('.woocommerce-loop-product__title, .wp-block-post-title');
		if (!title || card.querySelector('.lazada-promo-tag')) {
			return;
		}
		var tag = document.createElement('span');
		tag.className = 'lazada-promo-tag';
		tag.textContent = 'ส่งฟรี · ราคาพิเศษ';
		title.parentNode.insertBefore(tag, title);
	});

	if (!cards.length) {
		return;
	}

	if (!('IntersectionObserver' in window)) {
		cards.forEach(function (card) {
			card.classList.add('is-visible');
		});
		return;
	}

	var observer = new IntersectionObserver(function (entries) {
		entries.forEach(function (entry) {
			if (!entry.isIntersecting) {
				return;
			}
			entry.target.classList.add('is-visible');
			observer.unobserve(entry.target);
		});
	}, { rootMargin: '0px 0px -5% 0px', threshold: 0.06 });

	cards.forEach(function (card, index) {
		card.classList.add('lazada-reveal');
		card.style.transitionDelay = Math.min(index % 5, 4) * 35 + 'ms';
		observer.observe(card);
	});
})();
