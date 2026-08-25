/**
 * DRX Official Store - Track Order AJAX Modal Logic
 */

(function ($) {
	'use strict';

	$(document).ready(function () {
		// Open Track Order Modal
		$(document).on('click', '.drx-open-track-trigger', function (e) {
			e.preventDefault();
			openTrackModal();
		});

		// Close Track Order Modal
		$('#drxCloseTrackModal, #drxTrackOrderModal').on('click', function (e) {
			if (e.target === this || $(this).hasClass('close-modal') || $(this).hasClass('close-btn')) {
				closeTrackModal();
			}
		});

		// Submit Track Form
		$('#btnExecuteTrackOrder').on('click', function (e) {
			e.preventDefault();
			executeTrackOrder();
		});

		// Reset Track Form
		$('#btnResetTrackOrder').on('click', function (e) {
			e.preventDefault();
			resetTrackModal();
		});
	});

	function openTrackModal() {
		$('#drxTrackOrderModal').css('display', 'flex');
		resetTrackModal();
	}

	function closeTrackModal() {
		$('#drxTrackOrderModal').css('display', 'none');
	}

	function resetTrackModal() {
		$('#trackPhoneInput').val('');
		$('#trackOrderIdInput').val('');
		$('#trackFormArea').show();
		$('#trackResultArea').hide();
		$('#trackErrorMsg').hide();
	}

	function executeTrackOrder() {
		const phone = $('#trackPhoneInput').val().trim();
		const orderId = $('#trackOrderIdInput').val().trim().toUpperCase();
		const $btn = $('#btnExecuteTrackOrder');
		const $errorBox = $('#trackErrorMsg');
		const $errorText = $('#trackErrorText');

		$errorBox.hide();

		if (!phone || !orderId) {
			$errorText.text('Vui lòng nhập đầy đủ Số điện thoại và Mã đơn hàng.');
			$errorBox.css('display', 'flex');
			return;
		}

		$btn.text('ĐANG KIỂM TRA...').prop('disabled', true);

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
				if (res.success && res.data) {
					const data = res.data;
					let statusColor = '#10B981'; // Green
					if (data.status_slug === 'pending' || data.status_slug === 'processing' || data.status_slug === 'on-hold') {
						statusColor = '#F59E0B'; // Orange
					} else if (data.status_slug === 'cancelled' || data.status_slug === 'failed' || data.status_slug === 'refunded') {
						statusColor = '#EF4444'; // Red
					}

					let itemsHtml = '';
					if (data.items && data.items.length > 0) {
						itemsHtml = data.items.map(item => `
							<div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 6px; display:flex; justify-content:space-between;">
								<div>• ${item.product_name} <strong>(x${item.qty})</strong> ${item.custom_id ? `<span class="drx-badge-custom-id">ID: ${item.custom_id}</span>` : ''}</div>
								<div style="font-weight:600;">${item.total}</div>
							</div>
						`).join('');
					}

					const cardHtml = `
						<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
							<div>
								<div style="font-size: 11px; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">MÃ ĐƠN HÀNG</div>
								<div style="font-size: 17px; font-weight: 800; color: var(--text-primary); font-family: var(--font-heading);">${data.order_id}</div>
								<div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Ngày đặt: ${data.date}</div>
							</div>
							<div style="padding: 6px 12px; background: ${statusColor}18; color: ${statusColor}; border-radius: 6px; font-size: 12px; font-weight: 800; border: 1px solid ${statusColor}40;">
								${data.status}
							</div>
						</div>
						<div style="margin-bottom: 16px;">
							<div style="font-size: 11px; color: var(--text-muted); font-weight: 700; text-transform: uppercase; margin-bottom: 8px;">DANH SÁCH SẢN PHẨM</div>
							${itemsHtml || '<div style="font-size: 13px; color: var(--text-muted);">Không có chi tiết sản phẩm</div>'}
						</div>
						<div style="border-top: 1px solid var(--border-color); padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
							<div style="font-size: 13px; font-weight: 700; color: var(--text-primary);">TỔNG TIỀN:</div>
							<div style="font-size: 16px; font-weight: 800; color: var(--accent);">${data.total}</div>
						</div>
					`;

					$('#trackStatusCard').html(cardHtml);
					$('#trackFormArea').hide();
					$('#trackResultArea').show();
				} else {
					$errorText.text(res.data ? res.data.message : 'Không tìm thấy đơn hàng.');
					$errorBox.css('display', 'flex');
				}
				$btn.text('XÁC THỰC & TRA CỨU').prop('disabled', false);
			},
			error: function () {
				$errorText.text('Lỗi kết nối máy chủ khi tra cứu.');
				$errorBox.css('display', 'flex');
				$btn.text('XÁC THỰC & TRA CỨU').prop('disabled', false);
			}
		});
	}

})(jQuery);
