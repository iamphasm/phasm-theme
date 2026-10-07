/* PHASM Customizer controls: module sorter and quote repeater. */
( function ( $ ) {
	'use strict';

	function save( $control, data ) {
		$control.find( 'input.phasm-json' ).val( JSON.stringify( data ) ).trigger( 'change' );
	}

	/* ---------- Modules: drag and drop + on/off ---------- */
	function initModules( $control ) {
		var $list = $control.find( '.phasm-modules' );

		function collect() {
			var data = [];
			$list.children( 'li' ).each( function () {
				var $li = $( this );
				var on = $li.find( 'input.phasm-on' ).is( ':checked' );
				$li.toggleClass( 'is-off', ! on );
				data.push( {
					id: $li.data( 'id' ),
					on: on,
					scheme: $li.find( 'select' ).val(),
					divider: $li.find( 'input.phasm-divider' ).is( ':checked' )
				} );
			} );
			save( $control, data );
		}

		$list.sortable( {
			axis: 'y',
			handle: '.phasm-modules__handle, .phasm-modules__name',
			cancel: 'input, button, select',
			update: collect
		} );

		$list.on( 'change', 'input[type=checkbox], select', collect );

		$list.on( 'click', '.phasm-up, .phasm-down', function () {
			var $li = $( this ).closest( 'li' );
			if ( $( this ).hasClass( 'phasm-up' ) ) {
				$li.prev().before( $li );
			} else {
				$li.next().after( $li );
			}
			$( this ).trigger( 'focus' );
			collect();
		} );
	}

	/* ---------- Quotes: repeater ---------- */
	function initQuotes( $control ) {
		var $wrap = $control.find( '.phasm-quotes' );
		var $input = $control.find( 'input.phasm-json' );
		var quotes;

		try {
			quotes = JSON.parse( $input.val() || '[]' );
		} catch ( e ) {
			quotes = [];
		}
		if ( ! Array.isArray( quotes ) ) {
			quotes = [];
		}

		function collect() {
			var data = [];
			$wrap.children( '.phasm-quote' ).each( function ( i ) {
				var $row = $( this );
				$row.find( '.phasm-quote__num' ).text( '#' + ( i + 1 ) );
				data.push( {
					quote: $row.find( 'textarea' ).val(),
					author: $row.find( 'input' ).val()
				} );
			} );
			save( $control, data );
		}

		function addRow( q, focus ) {
			var uid = 'phasm-q-' + Math.random().toString( 36 ).slice( 2, 9 );
			var $row = $( '<div class="phasm-quote"></div>' );
			var $head = $( '<div class="phasm-quote__head"></div>' );
			$head.append( $( '<span class="phasm-quote__num"></span>' ) );
			$head.append(
				$( '<button type="button" class="button-link phasm-quote__remove"></button>' ).text( $wrap.data( 'label-remove' ) )
			);
			$row.append( $head );
			$row.append( $( '<label></label>' ).attr( 'for', uid + '-q' ).text( $wrap.data( 'label-quote' ) ) );
			$row.append( $( '<textarea rows="3"></textarea>' ).attr( 'id', uid + '-q' ).val( q.quote || '' ) );
			$row.append( $( '<label></label>' ).attr( 'for', uid + '-a' ).text( $wrap.data( 'label-author' ) ) );
			$row.append( $( '<input type="text">' ).attr( 'id', uid + '-a' ).val( q.author || '' ) );
			$wrap.append( $row );
			if ( focus ) {
				$row.find( 'textarea' ).trigger( 'focus' );
			}
		}

		quotes.forEach( function ( q ) {
			addRow( q, false );
		} );
		$wrap.children( '.phasm-quote' ).each( function ( i ) {
			$( this ).find( '.phasm-quote__num' ).text( '#' + ( i + 1 ) );
		} );

		$control.on( 'click', '.phasm-quotes__add', function () {
			addRow( {}, true );
			collect();
		} );
		$wrap.on( 'click', '.phasm-quote__remove', function () {
			$( this ).closest( '.phasm-quote' ).remove();
			collect();
		} );
		$wrap.on( 'input', 'textarea, input', collect );

		$wrap.sortable( { axis: 'y', handle: '.phasm-quote__head', cancel: 'button', update: collect } );
	}

	/* Run each control's setup once WordPress has embedded it in its section. */
	var api = wp.customize;
	api.controlConstructor.phasm_modules = api.Control.extend( {
		ready: function () {
			initModules( this.container );
		}
	} );
	api.controlConstructor.phasm_quotes = api.Control.extend( {
		ready: function () {
			initQuotes( this.container );
		}
	} );
}( jQuery ) );
