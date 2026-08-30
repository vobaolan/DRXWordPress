/**
 * DRX Official Store - JavaScript & Frontend Interactivity
 * Matches D:\DRX\store.html exactly while bridging with WordPress & WooCommerce AJAX.
 */

(function ($) {
	'use strict';

	let currentProductData = null;
	let selectedColor = null;
	let selectedSize = null;
	let currentQty = 1;
	let matchingVariant = null;

	$(document).ready(function () {
		initMarquee();
		initFilters();
		initEventHandlers();
	});

	/* --------------------------------------------------------------------------
	   1. Trending Now Marquee Auto-Scroll & Drag (from D:\DRX\store.html)
	   -------------------------------------------------------------------------- */
	function initMarquee() {
		const container = document.getElementById('marqueeContainer') || document.getElementById('drxMarqueeContainer');
		const track = document.getElementById('marqueeTrack') || document.getElementById('drxMarqueeTrack');
		if (!container || !track) return;

		let isDown = false;
		let startX, scrollLeft;
		const autoScrollSpeed = 1;

		container.addEventListener('mousedown', (e) => {
			isDown = true;
			container.style.cursor = 'grabbing';
			startX = e.pageX - container.offsetLeft;
			scrollLeft = container.scrollLeft;
		});

		container.addEventListener('mouseleave', () => {
			isDown = false;
			container.style.cursor = 'grab';
		});

		container.addEventListener('mouseup', () => {
			isDown = false;
			container.style.cursor = 'grab';
		});

		container.addEventListener('mousemove', (e) => {
			if (!isDown) return;
			e.preventDefault();
			const x = e.pageX - container.offsetLeft;
			const walk = (x - startX) * 2;
			container.scrollLeft = scrollLeft - walk;
		});

		function autoScroll() {
			if (!isDown) {
				container.scrollLeft += autoScrollSpeed;
				if (container.scrollLeft >= track.scrollWidth / 2) {
					container.scrollLeft = 0;
				}
			}
			requestAnimationFrame(autoScroll);
		}
		autoScroll();
	}

	/* --------------------------------------------------------------------------
	   2. Category & Real-time Search Filtering
	   -------------------------------------------------------------------------- */
	function initFilters() {
		$('.category-list a').on('click', function (e) {
			e.preventDefault();
			$('.category-list a').removeClass('active');
			$(this).addClass('active');
			filterProducts();
		});

		$('#searchInput, #drxSearchInput').on('keyup', function () {
			filterProducts();
		});
	}

	window.filterCat = function (cat, el) {
		$('.category-list a').removeClass('active');
		if (el) $(el).addClass('active');
		filterProducts();
	};

	window.handleSearch = function (val) {
		filterProducts();
	};

	function filterProducts() {
		const query = ($('#searchInput').val() || $('#drxSearchInput').val() || '').toLowerCase().trim();
		const $activeLink = $('.category-list a.active');
		const activeText = $activeLink.length ? $activeLink.text().toUpperCase().trim() : 'ALL';

		$('.product-card').each(function () {
			const $card = $(this);
			const title = ($card.find('.product-title').text() || $card.data('name') || '').toLowerCase();
			const cat = ($card.data('cat') || '').toString().toUpperCase();

			const matchesCategory = (activeText === 'ALL' || cat.indexOf(activeText) !== -1);
			const matchesQuery = (query === '' || title.indexOf(query) !== -1);

			if (matchesCategory && matchesQuery) {
				$card.show();
			} else {
				$card.hide();
			}
		});
	}

	/* --------------------------------------------------------------------------
	   3. Quick View Modal & Dynamic Options
	   -------------------------------------------------------------------------- */
	function initEventHandlers() {
		// Close modal on click outside
		$('#variantModal').on('click', function (e) {
			if (e.target === this) {
				closeVariantModal();
			}
		});
	}

	window.openProduct = function (productId) {
		openQuickView(productId);
	};

	window.openVariantSelector = function (productId) {
		openQuickView(productId);
	};

	window.closeVariantModal = function () {
		$('#variantModal').removeClass('active');
	};

	function openQuickView(productId) {
		$('#variantModal').addClass('active');
		$('#m_right_content').html('<div style="text-align:center; padding: 40px; color: var(--text-muted);">Đang tải thông tin sản phẩm...</div>');

		$.ajax({
			url: drx_ajax_obj.ajax_url,
			type: 'POST',
			data: {
				action: 'drx_get_product_details',
				product_id: productId,
				security: drx_ajax_obj.nonce
			},
			success: function (res) {
				if (res.success && res.data) {
					currentProductData = res.data;
					selectedColor = currentProductData.colors && currentProductData.colors.length === 1 ? currentProductData.colors[0] : null;
					selectedSize = currentProductData.sizes && currentProductData.sizes.length === 1 ? currentProductData.sizes[0] : null;
					currentQty = 1;
					matchingVariant = null;

					renderModalUI();
					checkMatchingVariant();
				} else {
					$('#m_right_content').html('<div style="color: #ef4444; padding: 20px;">Lỗi: ' + (res.data ? res.data.message : 'Không thể tải dữ liệu') + '</div>');
				}
			},
			error: function () {
				$('#m_right_content').html('<div style="color: #ef4444; padding: 20px;">Không thể kết nối máy chủ.</div>');
			}
		});
	}

	// Helper định dạng tiền Việt Nam Đồng (VNĐ)
	function formatPriceVND(amount) {
		const val = Math.round(Number(amount) || 0);
		return val.toLocaleString('vi-VN') + ' ₫';
	}

	function renderModalUI() {
		const p = currentProductData;
		const mainImg = p.images && p.images.length > 0 ? p.images[0] : 'https://teamdrx.vercel.app/thumbnail/20260727/aa447560a8495.png';
		$('#m_img').attr('src', mainImg);

		// Render Thumbnails
		let thumbsHtml = '';
		if (p.images && p.images.length > 1) {
			thumbsHtml = p.images.map((img, i) => `
				<div class="thumb-circle ${i === 0 ? 'active' : ''}" data-src="${img}">
					<img src="${img}" alt="Thumbnail">
				</div>
			`).join('');
		}
		$('#m_thumbnails').html(thumbsHtml);

		$('.thumb-circle').on('click', function () {
			const src = $(this).data('src');
			$('#m_img').attr('src', src);
			$('.thumb-circle').removeClass('active');
			$(this).addClass('active');
		});

		// Build Right Content
		let html = `
			<div class="m-title">${p.name}</div>
			<div class="m-price" id="m_price_display">${formatPriceVND(p.price)}</div>
			
			<div class="shipping-info">
				<div class="s-row"><div class="s-label">Domestic / International Shipping</div><div>oversea delivery</div></div>
				<div class="s-row"><div class="s-label">Payment for Shipping</div><div>Parcel Service</div></div>
				<div class="s-row"><div class="s-label">Shipping (Charge)</div><div>International Shipping Fee</div></div>
			</div>
		`;

		// Colors
		if (p.colors && p.colors.length > 0) {
			html += `<div class="attr-row"><div class="attr-label">Color</div><div class="attr-options" id="colorOptions">`;
			p.colors.forEach(c => {
				const isSel = selectedColor === c ? 'selected' : '';
				html += `<button type="button" class="attr-btn ${isSel}" data-color="${c}">${c}</button>`;
			});
			html += `</div></div>`;
		}

		// Sizes
		if (p.sizes && p.sizes.length > 0) {
			const isDashed = p.sizes.some(s => s.length <= 4) ? 'dashed' : '';
			html += `<div class="attr-row"><div class="attr-label">Size</div><div class="attr-options ${isDashed}" id="sizeOptions">`;
			p.sizes.forEach(s => {
				const isSel = selectedSize === s ? 'selected' : '';
				html += `<button type="button" class="attr-btn ${isSel}" data-size="${s}">${s}</button>`;
			});
			html += `</div></div>`;
		}

		// Custom Name / ID Embroidery
		if (p.allow_custom_id) {
			html += `
				<div class="attr-row" style="margin-top: 14px; grid-template-columns: 1fr;">
					<div class="attr-label" style="margin-bottom: 6px;">Custom Embroidered Name / ID (Optional):</div>
					<input type="text" id="m_custom_id" placeholder="Enter custom name (e.g. DEFT, FAKER)..." style="width:100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; font-family: var(--font-sans); font-size: 14px; outline: none; font-weight: 600; text-transform: uppercase;">
				</div>
			`;
		}

		// Quantity Stepper
		html += `
			<div class="qty-row">
				<div class="total-calc" style="font-weight: 400; color: var(--text-secondary);">total</div>
				<div style="display:flex; align-items:center; gap:20px;">
					<div class="m-price" id="calcPrice" style="margin-bottom:0; font-size: 18px;">${formatPriceVND(p.price * currentQty)}</div>
					<div class="qty-controls">
						<button type="button" class="qty-btn" id="btnQtyMinus">-</button>
						<span id="qtyVal" style="font-weight:700; width: 24px; text-align:center;">${currentQty}</span>
						<button type="button" class="qty-btn" id="btnQtyPlus">+</button>
					</div>
				</div>
			</div>
			
			<div class="modal-btn-group">
				<button type="button" class="btn-add-cart" id="btnModalAddCart" disabled>ADD TO CART</button>
				<button type="button" class="btn-buy-now" id="btnModalBuyNow" disabled>BUY NOW</button>
			</div>
		`;

		$('#m_right_content').html(html);

		// Event listener for color buttons
		$('#colorOptions .attr-btn').on('click', function () {
			selectedColor = $(this).data('color');
			$('#colorOptions .attr-btn').removeClass('selected');
			$(this).addClass('selected');
			checkMatchingVariant();
		});

		// Event listener for size buttons
		$('#sizeOptions .attr-btn').on('click', function () {
			selectedSize = $(this).data('size');
			$('#sizeOptions .attr-btn').removeClass('selected');
			$(this).addClass('selected');
			checkMatchingVariant();
		});

		// Stepper listeners
		$('#btnQtyMinus').on('click', function () {
			if (currentQty > 1) {
				currentQty--;
				$('#qtyVal').text(currentQty);
				updateCalculatedPrice();
			}
		});
		$('#btnQtyPlus').on('click', function () {
			currentQty++;
			$('#qtyVal').text(currentQty);
			updateCalculatedPrice();
		});

		// Button Add to cart & Buy now
		$('#btnModalAddCart').on('click', function () {
			executeAddToCart(false);
		});
		$('#btnModalBuyNow').on('click', function () {
			executeAddToCart(true);
		});
	}

	function checkMatchingVariant() {
		const p = currentProductData;
		if (!p) return;

		const hasColors = p.colors && p.colors.length > 0;
		const hasSizes = p.sizes && p.sizes.length > 0;

		let match = null;
		let isComplete = true;

		if (hasColors && !selectedColor) isComplete = false;
		if (hasSizes && !selectedSize) isComplete = false;

		if (isComplete && p.variants && p.variants.length > 0) {
			match = p.variants.find(v =>
				(!hasColors || v.color === selectedColor) &&
				(!hasSizes || v.size === selectedSize)
			);
		}

		matchingVariant = match;
		const $addBtn = $('#btnModalAddCart');
		const $buyBtn = $('#btnModalBuyNow');

		if (matchingVariant || (!hasColors && !hasSizes)) {
			updateCalculatedPrice();
			if (matchingVariant && matchingVariant.images && matchingVariant.images.length > 0) {
				$('#m_img').attr('src', matchingVariant.images[0]);
			}
			$addBtn.text('ADD TO CART').prop('disabled', false);
			$buyBtn.text('BUY NOW').prop('disabled', false);
		} else {
			if (isComplete) {
				$addBtn.text('SOLD OUT').prop('disabled', true);
				$buyBtn.text('SOLD OUT').prop('disabled', true);
			} else {
				$addBtn.text('SELECT OPTIONS').prop('disabled', true);
				$buyBtn.text('SELECT OPTIONS').prop('disabled', true);
			}
		}
	}

	function updateCalculatedPrice() {
		const price = matchingVariant ? matchingVariant.price : currentProductData.price;
		const total = price * currentQty;
		$('#calcPrice').text(formatPriceVND(total));
	}

	/* --------------------------------------------------------------------------
	   4. AJAX Add to Cart & Cart Drawer
	   -------------------------------------------------------------------------- */
	function executeAddToCart(redirectCheckout) {
		const customId = $('#m_custom_id').length ? $('#m_custom_id').val().trim() : '';
		const variationId = matchingVariant ? matchingVariant.id : 0;
		const productId = currentProductData.id;

		const $btn = redirectCheckout ? $('#btnModalBuyNow') : $('#btnModalAddCart');
		$btn.text('PROCESSING...').prop('disabled', true);

		$.ajax({
			url: drx_ajax_obj.ajax_url,
			type: 'POST',
			data: {
				action: 'drx_add_to_cart',
				product_id: productId,
				variation_id: variationId,
				quantity: currentQty,
				custom_id: customId,
				security: drx_ajax_obj.nonce
			},
			success: function (res) {
				if (res.success) {
					$('#variantModal').removeClass('active');
					$('#cartCount, .drx-cart-count').text(res.data.cart_count);
					renderCartDrawerItems(res.data);

					if (redirectCheckout) {
						window.location.href = res.data.checkout_url || drx_ajax_obj.checkout_url;
					} else {
						openCart();
					}
				} else {
					alert(res.data ? res.data.message : 'Error adding to cart');
				}
				$btn.text(redirectCheckout ? 'BUY NOW' : 'ADD TO CART').prop('disabled', false);
			},
			error: function () {
				alert('Server connection error.');
				$btn.text(redirectCheckout ? 'BUY NOW' : 'ADD TO CART').prop('disabled', false);
			}
		});
	}

	window.openCart = function () {
		$('#cartOverlay').addClass('active');
		$('#cartSidebar').addClass('active');
	};

	window.closeCart = function () {
		$('#cartOverlay').removeClass('active');
		$('#cartSidebar').removeClass('active');
	};

	function renderCartDrawerItems(data) {
		const $body = $('#cartBody');
		const $total = $('#cartTotal');

		if (!data.items || data.items.length === 0) {
			$body.html('<div class="cart-empty" style="text-align:center; padding: 40px; color: var(--text-muted);">Your cart is currently empty.</div>');
			$total.text('0 ₫');
			return;
		}

		let html = data.items.map(item => `
			<div class="cart-item">
				<img src="${item.image}" class="cart-item-img" alt="${item.product_name}">
				<div class="cart-item-details">
					<div class="cart-item-title">${item.product_name}</div>
					${item.custom_id ? `<div class="cart-item-meta" style="color: var(--accent); font-weight:600;">Custom ID: ${item.custom_id}</div>` : ''}
					<div class="cart-item-bottom">
						<span style="font-size: 13px; color: var(--text-muted);">Qty: ${item.quantity}</span>
						<span style="font-weight: 700; color: var(--accent); font-size: 14px;">${item.subtotal}</span>
					</div>
				</div>
			</div>
		`).join('');

		$body.html(html);
		$total.html(data.cart_total);
	}

	/* --------------------------------------------------------------------------
	   5. Track Order Modal Handlers (from D:\DRX\store.html)
	   -------------------------------------------------------------------------- */
	window.openTrackOrder = function () {
		document.getElementById('trackOrderModal').style.display = 'flex';
		resetTrackOrder();
	};

	window.closeTrackOrder = function () {
		document.getElementById('trackOrderModal').style.display = 'none';
	};

	window.resetTrackOrder = function () {
		$('#trackPhone').val('');
		$('#trackOrderId').val('');
		$('#trackFormArea').show();
		$('#trackResultArea').hide();
		$('#trackErrorMsg').hide();
	};

	window.executeTrackOrder = function () {
		const phone = $('#trackPhone').val().trim();
		const orderId = $('#trackOrderId').val().trim().toUpperCase();
		const $btn = $('#btnTrackOrder');
		const $errorBox = $('#trackErrorMsg');
		const $errorText = $('#trackErrorText');

		$errorBox.hide();

		if (!phone || !orderId) {
			$errorText.text("Please enter both Phone Number and Order ID.");
			$errorBox.show();
			return;
		}

		$btn.text("Searching...").prop('disabled', true);

		$.ajax({
			url: drx_ajax_obj.ajax_url,
			type: 'POST',
			data: {
				action: 'drx_track_order',
				phone: phone,
				order_id: orderId,
				security: drx_ajax_obj.nonce
			},
			success: function (res) {
				$btn.text("VERIFY & TRACK").prop('disabled', false);
				if (res.success && res.data) {
					const order = res.data;
					const statusClass = order.status.toUpperCase();
					let statusColor = '#0052FF';
					if (statusClass.includes('SHIP') || statusClass.includes('COMPLET')) statusColor = '#10B981';
					if (statusClass.includes('CANCEL')) statusColor = '#EF4444';

					let itemsHtml = (order.items || []).map(it => `
						<div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px;">
							<span>${it.name} (x${it.qty})</span>
							<span style="font-weight:600;">${it.subtotal}</span>
						</div>
					`).join('');

					$('#trackStatusCard').html(`
						<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid #E2E8F0; padding-bottom:12px;">
							<div>
								<div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">ORDER NUMBER</div>
								<div style="font-family:var(--font-heading); font-size:16px; font-weight:700; color:var(--text-primary);">${order.order_id}</div>
							</div>
							<span style="background:${statusColor}; color:white; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700; text-transform:uppercase;">${order.status}</span>
						</div>
						<div style="margin-bottom:14px;">${itemsHtml}</div>
						<div style="border-top:1px solid #E2E8F0; padding-top:10px; display:flex; justify-content:space-between; font-weight:700; font-size:15px;">
							<span>TOTAL:</span>
							<span style="color:var(--accent);">${order.total}</span>
						</div>
					`);
					$('#trackFormArea').hide();
					$('#trackResultArea').show();
				} else {
					$errorText.text(res.data ? res.data.message : "No matching order found.");
					$errorBox.show();
				}
			},
			error: function () {
				$btn.text("VERIFY & TRACK").prop('disabled', false);
				$errorText.text("Network error. Please try again.");
				$errorBox.show();
			}
		});
	};

})(jQuery);
