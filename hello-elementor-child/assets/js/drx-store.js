/**
 * DRX Official Store - Frontend Interactivity & WooCommerce Integration
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
	   1. Trending Now Marquee Auto-Scroll & Drag
	   -------------------------------------------------------------------------- */
	function initMarquee() {
		const container = document.getElementById('drxMarqueeContainer');
		const track = document.getElementById('drxMarqueeTrack');
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
		// Category click
		$('.category-list a').on('click', function (e) {
			e.preventDefault();
			$('.category-list a').removeClass('active');
			$(this).addClass('active');

			const selectedCategory = $(this).data('cat') ? $(this).data('cat').toString().toUpperCase() : 'ALL';
			filterProducts();
		});

		// Search input
		$('#drxSearchInput').on('keyup', function () {
			filterProducts();
		});
	}

	function filterProducts() {
		const query = $('#drxSearchInput').val().toLowerCase().trim();
		const activeCat = $('.category-list a.active').data('cat');
		const activeCatUpper = activeCat ? activeCat.toString().toUpperCase() : 'ALL';

		$('.product-card').each(function () {
			const $card = $(this);
			const title = $card.find('.product-title').text().toLowerCase();
			const cat = ($card.data('cat') || '').toString().toUpperCase();

			const matchesCategory = (activeCatUpper === 'ALL' || cat.indexOf(activeCatUpper) !== -1);
			const matchesQuery = (query === '' || title.indexOf(query) !== -1);

			if (matchesCategory && matchesQuery) {
				$card.fadeIn(200);
			} else {
				$card.fadeOut(200);
			}
		});
	}

	/* --------------------------------------------------------------------------
	   3. Quick View Modal & Dynamic Options
	   -------------------------------------------------------------------------- */
	function initEventHandlers() {
		// Open Quick View Modal
		$(document).on('click', '.drx-open-modal, .product-card', function (e) {
			e.preventDefault();
			const productId = $(this).data('product-id') || $(this).closest('.product-card').data('product-id');
			if (productId) {
				openQuickView(productId);
			}
		});

		// Close Quick View
		$('#drxCloseModal, #drxVariantModal').on('click', function (e) {
			if (e.target === this || $(this).hasClass('close-btn')) {
				$('#drxVariantModal').removeClass('active');
			}
		});

		// Cart Drawer Open/Close
		$(document).on('click', '.drx-open-cart-trigger', function (e) {
			e.preventDefault();
			openCartDrawer();
		});

		$('#drxCartOverlay, #drxCartClose').on('click', function () {
			closeCartDrawer();
		});
	}

	function openQuickView(productId) {
		$('#drxVariantModal').addClass('active');
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

	function renderModalUI() {
		const p = currentProductData;
		const mainImg = p.images && p.images.length > 0 ? p.images[0] : '';
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
			<div class="m-price" id="m_price_display">USD ${p.price.toFixed(2)}</div>
			
			<div class="shipping-info">
				<div class="s-row"><div class="s-label">Vận chuyển:</div><div>Giao hàng toàn quốc / Quốc tế</div></div>
				<div class="s-row"><div class="s-label">Đơn vị vận chuyển:</div><div>Giao hàng Hỏa tốc / Tiêu chuẩn</div></div>
				<div class="s-row"><div class="s-label">Đổi trả:</div><div>Hỗ trợ đổi size trong vòng 7 ngày</div></div>
			</div>
		`;

		// Colors
		if (p.colors && p.colors.length > 0) {
			html += `<div class="attr-row"><div class="attr-label">Màu sắc</div><div class="attr-options" id="colorOptions">`;
			p.colors.forEach(c => {
				const isSel = selectedColor === c ? 'selected' : '';
				html += `<button type="button" class="attr-btn ${isSel}" data-color="${c}">${c}</button>`;
			});
			html += `</div></div>`;
		}

		// Sizes
		if (p.sizes && p.sizes.length > 0) {
			html += `<div class="attr-row"><div class="attr-label">Kích cỡ</div><div class="attr-options dashed" id="sizeOptions">`;
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
					<div class="attr-label" style="margin-bottom: 6px;">Tên / In-Game ID may lên áo (Tùy chọn):</div>
					<input type="text" id="m_custom_id" placeholder="VD: FAKER, DEFT, TÊN BẠN..." style="width:100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; font-family: var(--font-sans); font-size: 14px; outline: none; font-weight: 600; text-transform: uppercase;">
				</div>
			`;
		}

		// Quantity Stepper
		html += `
			<div class="qty-row">
				<div style="font-weight: 600; color: var(--text-secondary);">TỔNG CỘNG:</div>
				<div style="display:flex; align-items:center; gap:20px;">
					<div class="m-price" id="calcPrice" style="margin-bottom:0; font-size: 18px;">USD ${(p.price * currentQty).toFixed(2)}</div>
					<div class="qty-controls">
						<button type="button" class="qty-btn" id="btnQtyMinus">-</button>
						<span id="qtyVal" style="font-weight:700; width: 24px; text-align:center;">${currentQty}</span>
						<button type="button" class="qty-btn" id="btnQtyPlus">+</button>
					</div>
				</div>
			</div>
			
			<div class="modal-btn-group">
				<button type="button" class="btn-add-cart" id="btnModalAddCart" disabled>THÊM VÀO GIỎ</button>
				<button type="button" class="btn-buy-now" id="btnModalBuyNow" disabled>MUA NGAY</button>
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

		if (isComplete) {
			match = p.variants.find(v =>
				(!hasColors || v.color === selectedColor) &&
				(!hasSizes || v.size === selectedSize)
			);
		}

		matchingVariant = match;
		const $addBtn = $('#btnModalAddCart');
		const $buyBtn = $('#btnModalBuyNow');

		if (matchingVariant) {
			updateCalculatedPrice();
			if (matchingVariant.images && matchingVariant.images.length > 0) {
				$('#m_img').attr('src', matchingVariant.images[0]);
			}
			$addBtn.text('THÊM VÀO GIỎ').prop('disabled', false);
			$buyBtn.text('MUA NGAY').prop('disabled', false);
		} else {
			if (isComplete) {
				$addBtn.text('HẾT HÀNG').prop('disabled', true);
				$buyBtn.text('HẾT HÀNG').prop('disabled', true);
			} else {
				$addBtn.text('CHỌN TÙY CHỌN').prop('disabled', true);
				$buyBtn.text('CHỌN TÙY CHỌN').prop('disabled', true);
			}
		}
	}

	function updateCalculatedPrice() {
		const price = matchingVariant ? matchingVariant.price : currentProductData.price;
		const total = price * currentQty;
		$('#calcPrice').text('USD ' + total.toFixed(2));
	}

	/* --------------------------------------------------------------------------
	   4. AJAX Add to Cart & Cart Drawer
	   -------------------------------------------------------------------------- */
	function executeAddToCart(redirectCheckout) {
		if (!matchingVariant && currentProductData.variants.length > 1) return;

		const customId = $('#m_custom_id').length ? $('#m_custom_id').val().trim() : '';
		const variationId = matchingVariant ? matchingVariant.id : 0;
		const productId = currentProductData.id;

		const $btn = redirectCheckout ? $('#btnModalBuyNow') : $('#btnModalAddCart');
		$btn.text('ĐANG XỬ LÝ...').prop('disabled', true);

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
					$('#drxVariantModal').removeClass('active');
					$('.drx-cart-count').text(res.data.cart_count);
					renderCartDrawerItems(res.data);

					if (redirectCheckout) {
						window.location.href = res.data.checkout_url || drx_ajax_obj.checkout_url;
					} else {
						openCartDrawer();
					}
				} else {
					alert(res.data ? res.data.message : 'Lỗi khi thêm vào giỏ hàng');
				}
				$btn.text(redirectCheckout ? 'MUA NGAY' : 'THÊM VÀO GIỎ').prop('disabled', false);
			},
			error: function () {
				alert('Lỗi kết nối máy chủ.');
				$btn.text(redirectCheckout ? 'MUA NGAY' : 'THÊM VÀO GIỎ').prop('disabled', false);
			}
		});
	}

	function openCartDrawer() {
		$('#drxCartOverlay').addClass('active');
		$('#drxCartSidebar').addClass('active');
	}

	function closeCartDrawer() {
		$('#drxCartOverlay').removeClass('active');
		$('#drxCartSidebar').removeClass('active');
	}

	function renderCartDrawerItems(data) {
		const $body = $('#drxCartBody');
		const $total = $('#drxCartTotal');

		if (!data.items || data.items.length === 0) {
			$body.html('<div class="cart-empty" style="text-align:center; padding: 40px; color: var(--text-muted);">Giỏ hàng của bạn đang trống.</div>');
			$total.text('$0.00');
			return;
		}

		let html = data.items.map(item => `
			<div class="cart-item" style="border-bottom: 1px solid var(--border-color); padding-bottom: 16px; margin-bottom: 16px; display: flex; gap: 14px;">
				<div style="width: 70px; height: 70px; background: #F8FAFC; border-radius: 8px; overflow: hidden; flex-shrink: 0;">
					<img src="${item.image}" style="width: 100%; height: 100%; object-fit: cover;" alt="Product">
				</div>
				<div style="flex: 1;">
					<div style="font-weight: 600; font-size: 13px; color: var(--text-primary); margin-bottom: 2px;">${item.product_name}</div>
					${item.custom_id ? `<div class="drx-badge-custom-id">ID: ${item.custom_id}</div>` : ''}
					<div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
						<div style="font-size: 12px; color: var(--text-secondary);">Số lượng: <strong>${item.quantity}</strong></div>
						<div style="font-weight: 700; font-size: 14px; color: var(--accent);">${item.subtotal}</div>
					</div>
				</div>
			</div>
		`).join('');

		$body.html(html);
		$total.html(data.cart_total);
	}

})(jQuery);
