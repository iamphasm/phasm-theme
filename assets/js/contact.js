/* PHASM contact form: open/close the modal, count characters, send with fetch. */
( function () {
	'use strict';

	var dialog = document.getElementById( 'phasm-contact' );
	if ( ! dialog || typeof dialog.showModal !== 'function' || ! window.phasmContact ) {
		return;
	}

	var cfg = window.phasmContact;
	var form = dialog.querySelector( '.contact-form' );
	var formView = dialog.querySelector( '.contact-modal__form-view' );
	var thanks = dialog.querySelector( '.contact-modal__thanks' );
	var status = dialog.querySelector( '.contact-form__status' );
	var message = dialog.querySelector( '#phasm-message' );
	var count = dialog.querySelector( '#phasm-count' );
	var submit = form.querySelector( 'button[type=submit]' );
	var submitHtml = submit.innerHTML;
	var opener = null;

	function updateCount() {
		count.textContent = message.value.length + ' / ' + cfg.max;
	}

	function clearErrors() {
		status.textContent = '';
		form.querySelectorAll( '.has-error' ).forEach( function ( el ) {
			el.classList.remove( 'has-error' );
			var input = el.querySelector( 'input, textarea' );
			if ( input ) {
				input.removeAttribute( 'aria-invalid' );
			}
			var err = el.querySelector( '.contact-form__error' );
			if ( err ) {
				err.remove();
			}
		} );
	}

	function showFieldError( name, text ) {
		var input = form.querySelector( '[name="' + name + '"]' );
		if ( ! input ) {
			return;
		}
		var field = input.closest( '.contact-form__field' );
		field.classList.add( 'has-error' );
		input.setAttribute( 'aria-invalid', 'true' );
		var err = document.createElement( 'span' );
		err.className = 'contact-form__error';
		err.textContent = text;
		field.appendChild( err );
	}

	function open( e ) {
		if ( e ) {
			e.preventDefault();
		}
		opener = document.activeElement;
		// Start fresh each time after a completed send.
		if ( ! thanks.hidden ) {
			thanks.hidden = true;
			formView.hidden = false;
			form.reset();
			updateCount();
		}
		dialog.showModal();
		document.documentElement.classList.add( 'phasm-modal-open' );
		var first = form.querySelector( '#phasm-name' );
		if ( first ) {
			first.focus();
		}
	}

	function close() {
		dialog.close();
	}

	dialog.addEventListener( 'close', function () {
		document.documentElement.classList.remove( 'phasm-modal-open' );
		if ( opener && opener.focus ) {
			opener.focus();
		}
	} );

	// Click on the blurred backdrop closes the form.
	dialog.addEventListener( 'click', function ( e ) {
		if ( e.target === dialog ) {
			close();
		}
	} );

	dialog.querySelectorAll( '[data-phasm-close]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', close );
	} );

	document.addEventListener( 'click', function ( e ) {
		var trigger = e.target.closest( '[data-phasm-contact], a[href$="#contact-form"]' );
		if ( trigger ) {
			open( e );
		}
	} );

	if ( window.location.hash === '#contact-form' ) {
		open();
	}

	message.addEventListener( 'input', updateCount );
	form.addEventListener( 'reset', function () {
		setTimeout( function () {
			clearErrors();
			updateCount();
		}, 0 );
	} );

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		clearErrors();

		// Quick check in the browser; the server checks everything again.
		var missing = false;
		[ 'name', 'email', 'message' ].forEach( function ( name ) {
			var input = form.querySelector( '[name="' + name + '"]' );
			if ( ! input.checkValidity() ) {
				showFieldError( name, input.validationMessage );
				missing = true;
			}
		} );
		if ( missing ) {
			form.querySelector( '[aria-invalid=true]' ).focus();
			return;
		}

		submit.disabled = true;
		submit.textContent = cfg.sending;

		fetch( cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: new FormData( form )
		} )
			.then( function ( res ) {
				return res.json().catch( function () {
					return { success: false };
				} );
			} )
			.then( function ( json ) {
				if ( json && json.success ) {
					formView.hidden = true;
					thanks.hidden = false;
					thanks.focus();
					form.reset();
					updateCount();
					return;
				}
				var data = ( json && json.data ) || {};
				status.textContent = data.message || cfg.error;
				if ( data.fields ) {
					Object.keys( data.fields ).forEach( function ( name ) {
						showFieldError( name, data.fields[ name ] );
					} );
				}
			} )
			.catch( function () {
				status.textContent = cfg.error;
			} )
			.finally( function () {
				submit.disabled = false;
				submit.innerHTML = submitHtml;
			} );
	} );

	updateCount();
}() );
