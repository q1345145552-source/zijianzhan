/**
 * TikTok 商城视觉增强：仅增加展示动画和平台文案，不修改 WooCommerce 行为。
 */
(function () {
	'use strict';

	if ('tiktok' !== document.documentElement.getAttribute('data-store-platform')) {
		return;
	}

	document.documentElement.classList.add('tiktok-store-ready');

	var badge = document.querySelector('.mall-shop-badge');
	if (badge) {
		badge.textContent = 'TREND SHOP · OFFICIAL';
	}

	var campaignLabel = document.querySelector('.mall-campaign-label');
	if (campaignLabel) {
		campaignLabel.textContent = 'LIVE TREND PICKS';
	}

	var cards = document.querySelectorAll('li.product, .wc-block-product');
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
	}, { rootMargin: '0px 0px -6% 0px', threshold: 0.08 });

	cards.forEach(function (card, index) {
		card.classList.add('tiktok-reveal');
		card.style.transitionDelay = Math.min(index % 6, 5) * 45 + 'ms';
		observer.observe(card);
	});
})();
