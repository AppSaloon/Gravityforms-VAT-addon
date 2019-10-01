<?php

GFForms::include_addon_framework();

class GFVatFieldAddOn extends GFAddOn {

	protected $_version = GF_VAT_FIELD_ADDON_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'vatfieldaddon';
	protected $_path = 'vatfieldaddon/vatfieldaddon.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Gravity Forms Vat Field Add-On';
	protected $_short_title = 'Vat Field Add-On';

	/**
	 * @var object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @return object $_instance An instance of this class.
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Include the field early so it is available when entry exports are being performed.
	 */
	public function pre_init() {
		parent::pre_init();

		if ( $this->is_gravityforms_supported() && class_exists( 'GF_Field' ) ) {
			require_once( 'includes/class-vat-gf-field.php' );
		}

		add_action( 'wp_ajax_validate_vat', array( $this, 'validate_vat' ) );
		add_action( 'wp_ajax_nopriv_validate_vat', array( $this, 'validate_vat' ) );
	}

	public function init_admin() {
		parent::init_admin();

		add_action( 'gform_editor_js', array( $this, 'editor_script' ) );
		add_action( 'gform_field_standard_settings', array( $this, 'my_advanced_settings' ), 10, 2 );
		add_filter( 'gform_tooltips', array( $this, 'tooltips' ) );
		add_action( 'gform_field_appearance_settings', array( $this, 'field_appearance_settings' ), 10, 2 );
		add_action( 'gform_editor_js_set_default_values', array( $this, 'add_default_vat_values' ) );
	}


	// # SCRIPTS & STYLES -----------------------------------------------------------------------------------------------

	public function editor_script() {
		?>
        <script type='text/javascript'>
            //adding setting to fields of type "text"
            fieldSettings.text += ", .vat_setting";

            function GetCustomizeVatUI( field ) {
                var imagesUrl = '<?php echo GFCommon::get_base_url() . '/images/'?>';
                var html, customLabel, isHidden, title, img, input, inputId, id, inputName, defaultLabel, placeholder;


                html = "<table class='field_custom_inputs_ui'><tr>";
                html += "<td><strong>" + <?php echo json_encode( esc_html__( 'Show',
					'gravityforms' ) ); ?>+"</strong></td>";
                html += "<td><strong><?php esc_html_e( 'Field', 'gravityforms' );?></strong></td></tr>";

                if ( field[ "inputs" ] === null ) {
                    return html;
                }

                for ( var i = 1; i < field[ "inputs" ].length; i++ ) {
                    input = field[ "inputs" ][ i ];
                    id = input.id;
                    inputName = 'input_' + id.toString();
                    inputId = inputName.replace( '.', '_' );
                    if ( jQuery( 'label[for="' + inputId + '"]' ).length == 0 ) {
                        continue;
                    }
                    isHidden = typeof input.isHidden != 'undefined' && input.isHidden ? true : false;
                    title = isHidden ? <?php echo json_encode( esc_html__( 'Inactive',
						'gravityforms' ) ); ?> : <?php echo json_encode( esc_html__( 'Active', 'gravityforms' ) ); ?>;
                    img = isHidden ? 'active0.png' : 'active1.png';
                    html += "<tr data-input_id='" + id + "' class='field_custom_input_row field_custom_input_row_" + inputId + "'>";

                    // show
                    html += "<td><img data-input_id='" + input.id + "' title='" + title + "' alt='" + title + "' class='input_active_icon' src='" + imagesUrl + img + "'/></td>";

                    if ( isHidden ) {
                        jQuery( "#input_" + inputId + "_container" ).toggle( !isHidden );
                    }
                    defaultLabel = typeof input.defaultLabel != 'undefined' ? input.defaultLabel : input.label;
                    defaultLabel = defaultLabel.replace( /'/g, "&#039;" );
                    html += "<td><label id='field_custom_input_default_label_" + inputId + "' for='field_custom_input_label_" + input.id + "' class='inline'>" + defaultLabel + "</label></td>";
                    html += "</tr>";
                }

                return html;
            }

            function UpgradeVatField( field ) {
                if ( field[ "inputs" ] !== undefined ) {
                    for ( var i = 1; i < field[ "inputs" ].length; i++ ) {
                        const inputId = field[ 'inputs' ][ i ].id.toString();
                        const number = inputId.replace( '.', '_' );

                        if ( jQuery( '#input_' + number + "_container" ).is( ':visible' ) === false ) {
                            field[ "inputs" ][ i ].isHidden = true;
                        }
                    }
                }

                return field;
            }

            /**
             * binding to the load field settings event to initialize the checkbox
             *
             * When the VAT block is clicked/extended
             **/
            jQuery( document ).on( "gform_load_field_settings", function ( event, field, form ) {
                /**
                 * FIELD type must be 'vat'
                 **/
                if ( field.type !== 'vat' ) {
                    return;
                }

                /**
                 *  Update VAT field in backend
                 **/
                SetDefaultValues( field );

                /**
                 *  Update VAT field (isHidden or not)
                 **/
                field = UpgradeVatField( field );

                /**
                 * Build the HTML
                 **/
                var vat_fields_str = GetCustomizeVatUI( field, true );
                jQuery( "#field_vat_fields_container" ).html( vat_fields_str );
            } );

            /**
             * Show toggle visible block
             */
            jQuery( '.vat_setting' )
                .on( 'click keypress', '.input_active_icon', function () {
                    var inputId = jQuery( this ).closest( '.field_custom_input_row' ).data( 'input_id' );
                    ToggleInputHidden( this, inputId );
                } )
        </script>
		<?php
	}

	/**
	 * Include my_script.js when the form contains a 'vat' type field.
	 *
	 * @return array
	 */
	public function scripts() {
		$scripts = array(
			array(
				'handle'  => 'vat_field',
				'src'     => $this->get_base_url() . '/js/vat-field.js',
				'version' => $this->_version,
				'deps'    => array( 'jquery' ),
				'enqueue' => array(
					array( 'field_types' => array( 'vat' ) ),
				),
				'strings' => array(
					'ajax_url' => admin_url( 'admin-ajax.php' ) . '?action=validate_vat',
				),
			),

		);

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Include my_styles.css when the form contains a 'vat' type field.
	 *
	 * @return array
	 */
	public function styles() {
		$styles = array(
			array(
				'handle'  => 'my_styles_css',
				'src'     => $this->get_base_url() . '/css/my_styles.css',
				'version' => $this->_version,
				'enqueue' => array(
					array( 'field_types' => array( 'vat' ) ),
				),
			),
		);

		return array_merge( parent::styles(), $styles );
	}


	// # FIELD SETTINGS -------------------------------------------------------------------------------------------------

	public function my_advanced_settings( $position, $form_id ) {

		//create settings on position 1050 (right after gform_field_standard_settings)
		if ( $position == 1100 ) {
			/* @var GF_Field_Address $gf_address_field */
			?>
            <li class="vat_setting field_setting">
                <label for="field_admin_label" class="section_label inline">
					<?php _e( "Vat", "gravityforms" ); ?>
					<?php gform_tooltip( "form_field_vat_value" ) ?>
                </label>

                <div id="field_vat_fields_container" style="padding-top:10px;">
                    <!-- content dynamically created from js.php -->
                </div>


            </li>
			<?php
		}
	}

	/**
	 * Add the tooltips for the field.
	 *
	 * @param  array  $tooltips  An associative array of tooltips where the key is the tooltip name and the value is the tooltip.
	 *
	 * @return array
	 */
	public function tooltips( $tooltips ) {
		$tooltips['input_class_setting'] = sprintf( '<h6>%s</h6>%s', esc_html__( 'Input CSS Classes', 'vatfieldaddon' ),
			esc_html__( 'The CSS Class names to be added to the field input.', 'vatfieldaddon' ) );

		$tooltips['form_field_vat_value'] = "<h6>VAT</h6>VAT field settings.";

		return $tooltips;
	}

	/**
	 * Add the custom setting for the Vat field to the Appearance tab.
	 *
	 * @param  int  $position  The position the settings should be located at.
	 * @param  int  $form_id  The ID of the form currently being edited.
	 */
	public function field_appearance_settings( $position, $form_id ) {
		// Add our custom setting just before the 'Custom CSS Class' setting.
		if ( $position == 250 ) {
			?>
            <li class="input_class_setting field_setting">
                <label for="input_class_setting">
					<?php esc_html_e( 'Input CSS Classes', 'vatfieldaddon' ); ?>
					<?php gform_tooltip( 'input_class_setting' ) ?>
                </label>
                <input id="input_class_setting" type="text" class="fieldwidth-1"
                       onkeyup="SetInputClassSetting(jQuery(this).val());"
                       onchange="SetInputClassSetting(jQuery(this).val());"/>
            </li>

			<?php
		}
	}

	public function validate_vat() {
		$vat_nonce = sanitize_text_field( $_POST['vat_nonce'] );

		if ( ! wp_verify_nonce( $vat_nonce, 'vat_validation' ) ) {
			wp_send_json_error( array( 'msg' => 'Nonce failed' ) );
		}

		$vat          = sanitize_text_field( $_POST['vat'] );
		$country_code = substr( $vat, 0, 2 );
		$vat_number   = str_replace( ' ', '', substr( $vat, 2 ) );

		$response = array();
		$vies     = new \DragonBe\Vies\Vies();

		try {
			if ( false === $vies->getHeartBeat()->isAlive() ) {

				$response['error_msg'] = __( 'Service is not available at the moment, please try again later.',
					'vatfieldaddon' );

				wp_send_json_error( $response );
			}

			$validated = $vies->validateVat( $country_code, $vat_number );

			if ( $validated->isValid() ) {
				$response = array(
					'vat_number'      => $validated->getVatNumber(),
					'country_code'    => $validated->getCountryCode(),
					'company_address' => str_replace( PHP_EOL, ' ', $validated->getAddress() ),
					'company_name'    => $validated->getName(),
				);

				wp_send_json_success( $response );
			} else {
				$response = array(
					'error_msg' => __( 'VAT Number is not valid', 'vatfieldaddon' ),
				);

				wp_send_json_error( $response );
			}

		} catch ( Exception $e ) {
			$response['error_msg'] = sprintf( __( 'Service is not available at the moment, please try again later. (%s)',
				'vatfieldaddon' ), $e->getMessage() );

			wp_send_json_error( $response );
		}
	}

	public function add_default_vat_values() {
		//this hook is fired in the middle of a switch statement,
		//so we need to add a case for our new field type
		?>
        case 'vat':
        if (!field.label)
        field.label = <?php echo json_encode( esc_html__( 'VAT', 'gravityforms' ) ); ?>;
        field.inputs = [
        new Input(field.id + 0.1, <?php echo json_encode( gf_apply_filters( array( 'gform_vat', rgget( 'id' ) ),
			esc_html__( 'VAT number', 'gravityforms' ), rgget( 'id' ) ) ); ?>),
        new Input(field.id + 0.2, <?php echo json_encode( gf_apply_filters( array(
			'gform_company_address',
			rgget( 'id' ),
		), esc_html__( 'Company Address', 'gravityforms' ), rgget( 'id' ) ) ); ?>),
        new Input(field.id + 0.3, <?php echo json_encode( gf_apply_filters( array(
			'gform_company_name',
			rgget( 'id' ),
		), esc_html__( 'Company Name', 'gravityforms' ), rgget( 'id' ) ) ); ?>),
        new Input(field.id + 0.6, <?php echo json_encode( gf_apply_filters( array(
			'gform_address_country',
			rgget( 'id' ),
		), esc_html__( 'Country', 'gravityforms' ), rgget( 'id' ) ) ); ?>)
        ];

        break;

		<?php
	}
}