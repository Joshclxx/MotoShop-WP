/**
 * SwiftCart COD — Admin JS
 *
 * Handles inline order status updates and blacklist toggling via AJAX.
 *
 * @package SwiftCart
 * @since   1.0.0
 */

/* global scAdmin, jQuery */
( function ( $ ) {
	'use strict';

	$( document ).on( 'click', '.sc-action-btn', function () {
		const $btn    = $( this );
		const orderId = $btn.data( 'order' );
		const action  = $btn.data( 'action' );

		$btn.prop( 'disabled', true ).text( '…' );

		$.post( scAdmin.ajaxUrl, {
			action:   'sc_update_order_status',
			order_id: orderId,
			status:   action,
			nonce:    scAdmin.nonce,
		} ).done( function ( res ) {
			if ( res.success ) {
				location.reload();
			} else {
				alert( res.data?.message || 'Error updating order.' );
				$btn.prop( 'disabled', false );
			}
		} ).fail( function () {
			alert( 'Request failed. Please try again.' );
			$btn.prop( 'disabled', false );
		} );
	} );

	// Warehouse action buttons (used on /warehouse/* pages).
	$( document ).on( 'click', '.sc-wh-action', function () {
		const $btn     = $( this );
		const orderId  = $btn.data( 'order' );
		const action   = $btn.data( 'action' );
		const redirect = $btn.data( 'redirect' );

		$btn.prop( 'disabled', true ).text( '…' );

		$.post( scAdmin.ajaxUrl || window.location.origin + '/wp-admin/admin-ajax.php', {
			action:    'sc_warehouse_action',
			wh_action: action,
			order_id:  orderId,
			nonce:     $( 'input[name="sc_wh_nonce"]' ).val() || '',
		} ).done( function ( res ) {
			if ( res.success ) {
				if ( redirect ) {
					window.location.href = redirect;
				} else {
					location.reload();
				}
			} else {
				alert( res.data?.message || 'Action failed.' );
				$btn.prop( 'disabled', false ).text( 'Retry' );
			}
		} );
	} );

	// Select all checkbox.
	$( '#sc-select-all' ).on( 'change', function () {
		$( 'input[name="sc_order_ids[]"]' ).prop( 'checked', this.checked );
	} );

} )( jQuery );
