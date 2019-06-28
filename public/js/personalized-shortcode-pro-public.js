var isSnapchat = navigator.userAgent.toLowerCase().indexOf( 'snapchat' ) >= 0;

function onSnapchatPageShow() {
	runPersonalization();
}

// Run when not Snapchat
if ( ! ( wp_vars.snapchat_preload && isSnapchat ) ) {
	runPersonalization();
}

function runPersonalization() {
	jQuery( document ).ready(
		function( $ ) {

			var valuesToCheck = [], conditionalValuesToCheck = [];

			$( '.psp-type' ).each( function( i, item ) {
				valuesToCheck.push( $( this ).data( 'psp-type' ) );
			});

			if ( valuesToCheck.length > 0 ) {

				var data = {
					action: 'psp_get_user_data',
					values: valuesToCheck,
					security: wp_vars.security,
					testip: wp_vars.testing_ip
				};

				$.post( wp_vars.ajaxurl, data, function( response ) {

					if ( response.data && response.data.length ) {

						$.each( response.data, function( i, item ) {

							if ( item.value ) {

								$( '[data-psp-type="' + item.type + '"' ).each( function( i, el ) {
									$( el ).text( item.value );
								});
							}
						});
					}
				} );
			}

			$( '.psp-conditional' ).each( function( i, item ) {
				conditionalValuesToCheck.push( {
					content: $( this ).data( 'psp-content' ),
					values: $( this ).data( 'psp-values' ),
					type: $( this ).data( 'psp-type' ),
					exclude: $( this ).data( 'psp-exclude' ),
					id: $( this ).data( 'psp-id' )
				} );
			});

			if ( conditionalValuesToCheck.length > 0 ) {

				var data = {
					action: 'psp_conditional_content',
					values: conditionalValuesToCheck,
					security: wp_vars.security,
					testip: wp_vars.testing_ip
				};

				$.post( wp_vars.ajaxurl, data, function( response ) {

					if ( response.data && response.data.length ) {

						$.each( response.data, function( i, item ){

							if ( item.id ) {

								$( '[data-psp-id="' + item.id + '"' ).each( function( i, el ) {
									$( el ).text( item.content );
								});
							}
						});
					}
				} );
			}
		}
	);
}
