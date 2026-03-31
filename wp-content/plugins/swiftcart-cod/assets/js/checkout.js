/**
 * SwiftCart COD — 3-Step Checkout Controller
 *
 * Wraps the standard WooCommerce checkout form into a 3-step mobile-first UI:
 *   Step 1 — Address (contact info + PH address cascade)
 *   Step 2 — Shipping (method selection + cut-off timer)
 *   Step 3 — COD Confirmation (order summary + COD block + place order)
 *
 * Province → City → Barangay cascading dropdowns populated via AJAX.
 *
 * @package SwiftCart
 * @since   1.0.0
 */

/* global swiftcartCheckout, jQuery, wc_checkout_params */
( function ( $ ) {
	'use strict';

	/** ── Constants ─────────────────────────────────────────────────────────── */
	const cfg     = swiftcartCheckout;
	const STEPS   = [ 'address', 'shipping', 'confirm' ];
	let   current = 0;

	/** ── DOM helpers ───────────────────────────────────────────────────────── */
	const qs  = ( sel, ctx = document ) => ctx.querySelector( sel );
	const qsa = ( sel, ctx = document ) => [ ...ctx.querySelectorAll( sel ) ];

	/** ── Build the progress bar ────────────────────────────────────────────── */
	function buildProgressBar() {
		const labels = [
			cfg.i18n.stepAddress,
			cfg.i18n.stepShipping,
			cfg.i18n.stepConfirm,
		];

		const bar = document.createElement( 'div' );
		bar.className = 'sc-checkout-progress';
		bar.id        = 'sc-progress-bar';

		labels.forEach( ( label, idx ) => {
			const step = document.createElement( 'div' );
			step.className   = 'sc-checkout-step';
			step.dataset.step = String( idx );
			step.innerHTML   = `<span class="sc-checkout-step__circle">${ idx + 1 }</span><span class="sc-checkout-step__label">${ label }</span>`;
			bar.appendChild( step );

			if ( idx < labels.length - 1 ) {
				const conn = document.createElement( 'div' );
				conn.className       = 'sc-checkout-connector';
				conn.dataset.after   = String( idx );
				bar.appendChild( conn );
			}
		} );

		const form = qs( 'form.checkout' );
		if ( form ) {
			form.parentNode.insertBefore( bar, form );
		}
	}

	/** ── Wrap the checkout form into 3 sections ────────────────────────────── */
	function buildSections() {
		const form = qs( 'form.checkout' );
		if ( ! form ) return;

		// Address fields — everything inside #customer_details.
		const customerDetails = qs( '#customer_details', form );
		if ( customerDetails ) {
			wrapSection( customerDetails, 'address', cfg.i18n.stepAddress );
		}

		// Shipping — the #order_review containing shipping methods.
		const orderReview = qs( '#order_review', form );
		if ( orderReview ) {
			wrapSection( orderReview, 'shipping', cfg.i18n.stepShipping );
		}

		// Confirm — clone order totals + COD block above place order button.
		buildConfirmSection( form );
	}

	/** Wrap an existing node into a section div. */
	function wrapSection( node, stepId, label ) {
		const wrapper = document.createElement( 'div' );
		wrapper.className   = 'sc-checkout-section';
		wrapper.id          = `sc-step-${ stepId }`;
		wrapper.dataset.step = STEPS.indexOf( stepId ).toString();

		const header = document.createElement( 'h2' );
		header.className   = 'sc-checkout-section__header';
		header.textContent = label;

		node.parentNode.insertBefore( wrapper, node );
		wrapper.appendChild( header );
		wrapper.appendChild( node );

		return wrapper;
	}

	/** Build the confirm section with COD block, terms, and place-order button. */
	function buildConfirmSection( form ) {
		const section = document.createElement( 'div' );
		section.className    = 'sc-checkout-section';
		section.id           = 'sc-step-confirm';
		section.dataset.step = '2';

		const header = document.createElement( 'h2' );
		header.className   = 'sc-checkout-section__header';
		header.textContent = cfg.i18n.stepConfirm;
		section.appendChild( header );

		// COD confirmation block (total filled dynamically).
		const codBlock = document.createElement( 'div' );
		codBlock.className = 'sc-cod-confirm-block';
		codBlock.id        = 'sc-cod-block';
		codBlock.innerHTML = `
			<div class="sc-cod-confirm-block__icon">💵</div>
			<div class="sc-cod-confirm-block__title">Cash on Delivery</div>
			<div class="sc-cod-confirm-block__amount" id="sc-cod-amount">—</div>
			<div style="margin-top:8px;font-size:13px;color:#555;">Prepare this exact amount for our rider.</div>
		`;
		section.appendChild( codBlock );

		// Verification call notice (shown when total ≥ threshold).
		const verifyNotice = document.createElement( 'div' );
		verifyNotice.id        = 'sc-verify-notice';
		verifyNotice.className = 'sc-anti-scam-notice';
		verifyNotice.style.display = 'none';
		verifyNotice.textContent   = cfg.i18n.verifyCall;
		section.appendChild( verifyNotice );

		// COD terms box.
		const terms = document.createElement( 'div' );
		terms.className = 'sc-cod-terms';
		terms.innerHTML = `
			<strong>Order Terms</strong><br>
			By confirming, you agree to receive this order and pay the exact COD amount to our rider.<br><br>
			⚠️ Repeated cancellations may result in your number being blocked from future orders.<br>
			Our rider will call your mobile number before delivery.
		`;
		section.appendChild( terms );

		// Anti-scam clause.
		const antiScam = document.createElement( 'div' );
		antiScam.className = 'sc-anti-scam-notice';
		antiScam.innerHTML = `
			🚨 <strong>Important:</strong> SwiftCart COD will <strong>never</strong> ask for prepayment,
			bank transfers, or GCash before delivery. If anyone contacts you claiming to be us and
			asks for upfront payment — it is a scam.
		`;
		section.appendChild( antiScam );

		// Move the actual place-order button inside this section.
		const placeOrderWrap = qs( '#place_order', form );
		if ( placeOrderWrap ) {
			const placeOrderBtn = placeOrderWrap.closest( '.form-row' ) || placeOrderWrap;
			section.appendChild( placeOrderBtn );
		}

		form.appendChild( section );
	}

	/** ── Navigation buttons ────────────────────────────────────────────────── */
	function buildNavButtons() {
		STEPS.forEach( ( stepId, idx ) => {
			const section = qs( `#sc-step-${ stepId }` );
			if ( ! section ) return;

			const nav = document.createElement( 'div' );
			nav.className = 'sc-checkout-nav';

			if ( idx > 0 ) {
				const back = document.createElement( 'button' );
				back.type      = 'button';
				back.className = 'sc-btn sc-btn--secondary';
				back.textContent = cfg.i18n.back;
				back.addEventListener( 'click', () => goTo( idx - 1 ) );
				nav.appendChild( back );
			}

			if ( idx < STEPS.length - 1 ) {
				const next = document.createElement( 'button' );
				next.type      = 'button';
				next.className = 'sc-btn';
				next.textContent = cfg.i18n.continue;
				next.addEventListener( 'click', () => validateAndAdvance( idx ) );
				nav.appendChild( next );
			}

			if ( nav.children.length ) {
				section.appendChild( nav );
			}
		} );
	}

	/** ── Go to a specific step ─────────────────────────────────────────────── */
	function goTo( idx ) {
		if ( idx < 0 || idx >= STEPS.length ) return;

		current = idx;

		qsa( '.sc-checkout-section' ).forEach( ( s ) => s.classList.remove( 'sc-checkout-section--active' ) );

		const target = qs( `#sc-step-${ STEPS[ idx ] }` );
		if ( target ) {
			target.classList.add( 'sc-checkout-section--active' );
			target.scrollIntoView( { behavior: 'smooth', block: 'start' } );
		}

		updateProgressBar( idx );

		if ( 'shipping' === STEPS[ idx ] ) {
			updateCutoffTimer();
		}

		if ( 'confirm' === STEPS[ idx ] ) {
			updateCODAmount();
		}
	}

	/** ── Validate fields in the current step before advancing ──────────────── */
	function validateAndAdvance( idx ) {
		const section = qs( `#sc-step-${ STEPS[ idx ] }` );
		if ( ! section ) {
			goTo( idx + 1 );
			return;
		}

		const requiredFields = qsa( '[required]', section );
		let valid = true;

		requiredFields.forEach( ( field ) => {
			field.classList.remove( 'sc-field-error' );
			if ( '' === field.value.trim() ) {
				field.classList.add( 'sc-field-error' );
				field.style.borderColor = '#D32F2F';
				valid = false;
			} else {
				field.style.borderColor = '';
			}
		} );

		if ( ! valid ) {
			requiredFields.find( ( f ) => '' === f.value.trim() )?.focus();
			return;
		}

		goTo( idx + 1 );
	}

	/** ── Update the progress bar ───────────────────────────────────────────── */
	function updateProgressBar( idx ) {
		qsa( '.sc-checkout-step' ).forEach( ( el, i ) => {
			el.classList.remove( 'sc-checkout-step--active', 'sc-checkout-step--done' );
			const circle = qs( '.sc-checkout-step__circle', el );

			if ( i < idx ) {
				el.classList.add( 'sc-checkout-step--done' );
				if ( circle ) circle.textContent = '✓';
			} else if ( i === idx ) {
				el.classList.add( 'sc-checkout-step--active' );
				if ( circle ) circle.textContent = String( i + 1 );
			} else {
				if ( circle ) circle.textContent = String( i + 1 );
			}
		} );

		qsa( '.sc-checkout-connector' ).forEach( ( el, i ) => {
			el.classList.toggle( 'sc-checkout-connector--done', i < idx );
		} );
	}

	/** ── Cascade dropdowns: Province → City → Barangay ─────────────────────── */
	function initCascade() {
		const provinceSelect  = qs( 'select#billing_province, select.sc-province-select' );
		const cityInput       = qs( '#billing_city' );
		const barangayInput   = qs( '#billing_barangay' );

		if ( ! provinceSelect || ! cityInput ) return;

		// Convert city input to a datalist or managed dropdown.
		const cityDatalist = document.createElement( 'datalist' );
		cityDatalist.id    = 'sc-city-list';
		cityInput.setAttribute( 'list', 'sc-city-list' );
		cityInput.after( cityDatalist );

		provinceSelect.addEventListener( 'change', function () {
			const province = this.value;

			cityInput.value   = '';
			cityDatalist.innerHTML = '';

			if ( barangayInput ) barangayInput.value = '';

			if ( ! province ) return;

			cityInput.placeholder = cfg.i18n.loading;

			$.post( cfg.ajaxUrl, {
				action:   'swiftcart_get_cities',
				nonce:    cfg.nonce,
				province: province,
			} ).done( function ( res ) {
				cityInput.placeholder = cfg.i18n.selectCity;

				if ( res.success && res.data.cities ) {
					res.data.cities.forEach( ( city ) => {
						const opt = document.createElement( 'option' );
						opt.value = city;
						cityDatalist.appendChild( opt );
					} );
				}
			} );
		} );

		cityInput.addEventListener( 'change', function () {
			const province = provinceSelect.value;
			const city     = this.value;

			if ( barangayInput ) barangayInput.value = '';
			if ( ! province || ! city ) return;

			$.post( cfg.ajaxUrl, {
				action:   'swiftcart_get_barangays',
				nonce:    cfg.nonce,
				province: province,
				city:     city,
			} ).done( function ( res ) {
				if ( res.success && res.data.barangays && barangayInput ) {
					// Build a datalist for barangay suggestions.
					let dataList = qs( '#sc-barangay-list' );
					if ( ! dataList ) {
						dataList = document.createElement( 'datalist' );
						dataList.id = 'sc-barangay-list';
						barangayInput.setAttribute( 'list', 'sc-barangay-list' );
						barangayInput.after( dataList );
					}

					dataList.innerHTML = '';
					res.data.barangays.forEach( ( brgy ) => {
						const opt = document.createElement( 'option' );
						opt.value = brgy;
						dataList.appendChild( opt );
					} );
				}
			} );
		} );
	}

	/** ── Cut-off timer ──────────────────────────────────────────────────────── */
	function updateCutoffTimer() {
		let container = qs( '#sc-cutoff-timer' );
		if ( ! container ) {
			container = document.createElement( 'div' );
			container.id        = 'sc-cutoff-timer';
			container.className = 'sc-cutoff-notice';

			const shippingSection = qs( '#sc-step-shipping' );
			if ( shippingSection ) {
				const firstChild = shippingSection.querySelector( '.sc-checkout-section__header' );
				if ( firstChild ) {
					firstChild.after( container );
				} else {
					shippingSection.prepend( container );
				}
			}
		}

		function tick() {
			const now       = new Date();
			const cutoff    = new Date();
			cutoff.setHours( cfg.cutoffHour, 0, 0, 0 );

			const diffMs   = cutoff - now;
			const isWarning = diffMs > 0 && diffMs < 30 * 60 * 1000; // < 30 min.
			const isPast    = diffMs <= 0;

			const timeStr = now.toLocaleTimeString( 'en-PH', { hour: '2-digit', minute: '2-digit' } );

			container.classList.toggle( 'sc-cutoff-notice--warning', isWarning );

			if ( isPast ) {
				container.innerHTML = `<span class="sc-cutoff-notice__clock">🕐</span> Cutoff has passed. Your order will be dispatched tomorrow.`;
			} else if ( isWarning ) {
				const minsLeft = Math.ceil( diffMs / 60000 );
				container.innerHTML = `<span class="sc-cutoff-notice__clock">⚠️</span> Cutoff in <strong>${ minsLeft } minutes</strong>! Order now for same-day dispatch.`;
			} else {
				container.innerHTML = `<span class="sc-cutoff-notice__clock">✅</span> Order before <strong>${ cfg.cutoffHour }:00 PM</strong> for same-day dispatch. Current time: ${ timeStr }`;
			}
		}

		tick();
		setInterval( tick, 30000 );
	}

	/** ── Update COD amount in step 3 ──────────────────────────────────────── */
	function updateCODAmount() {
		const totalEl     = qs( '.order-total .woocommerce-Price-amount' ) ||
		                    qs( '#sc-cod-amount' );
		const codAmountEl = qs( '#sc-cod-amount' );
		const verifyEl    = qs( '#sc-verify-notice' );

		if ( ! codAmountEl ) return;

		const totalText = totalEl ? totalEl.textContent.trim() : '—';
		codAmountEl.textContent = totalText;

		// Show verification notice if total exceeds threshold.
		const raw = parseFloat( totalText.replace( /[^0-9.]/g, '' ) );
		if ( verifyEl ) {
			verifyEl.style.display = ( raw >= cfg.callThreshold ) ? 'block' : 'none';
		}
	}

	/** ── Override WooCommerce's place-order button text ─────────────────────── */
	function customisePlaceOrderButton() {
		const btn = qs( '#place_order' );
		if ( btn ) {
			btn.value     = cfg.i18n.placeOrder;
			btn.className += ' sc-btn';
		}
	}

	/** ── Init ───────────────────────────────────────────────────────────────── */
	$( document ).ready( function () {
		if ( ! qs( 'form.checkout' ) ) return;

		buildProgressBar();
		buildSections();
		buildNavButtons();
		customisePlaceOrderButton();
		initCascade();

		goTo( 0 );
	} );

	// Re-render COD amount when WooCommerce updates the checkout via AJAX.
	$( document.body ).on( 'updated_checkout', function () {
		if ( 2 === current ) {
			updateCODAmount();
		}
	} );

} )( jQuery );
