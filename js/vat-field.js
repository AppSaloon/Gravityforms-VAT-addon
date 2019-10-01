jQuery( document ).on( 'change', '.vat_number input', function () {
    // get specific field ID
    const parentId = jQuery( this ).parent().parent().attr( 'id' );

    // validate VAT
    callVatService( this.value, parentId );
} );

/**
 * Validate the VAT number
 *
 * @param vat
 * @param parentId
 */
function callVatService( vat, parentId ) {
    const data = {
        'vat': vat,
        'vat_nonce': jQuery( '#vat_nonce' ).val()
    };

    // AJAX call to validate VAT
    jQuery.ajax( {
        url: vat_field_strings.ajax_url,
        type: 'POST',
        data: data,
        success: function ( response, textStatus, jQxhr ) {
            if ( response.success === false ) {
                console.log( response.data.error_msg );
            } else {
                jQuery( '#' + parentId + ' .vat_company_address input' ).val( response.data.company_address );
                jQuery( '#' + parentId + ' .vat_company_name input' ).val( response.data.company_name );
                jQuery( '#' + parentId + ' .vat_country select' ).val( response.data.country_code );
            }
        }
    } );
}