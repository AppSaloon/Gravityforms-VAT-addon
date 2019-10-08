<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class Vat_GF_Field extends GF_Field {

	/**
	 * @var string $type The field type.
	 */
	public $type = 'vat';

	/**
	 * Return the field title, for use in the form editor.
	 *
	 * @return string
	 */
	public function get_form_editor_field_title() {
		return esc_attr__( 'VAT', 'vatfieldaddon' );
	}

	/**
	 * Assign the field button to the Advanced Fields group.
	 *
	 * @return array
	 */
	public function get_form_editor_button() {
		return array(
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title(),
		);
	}

	/**
	 * The settings which should be available on the field in the form editor.
	 *
	 * @return array
	 */
	function get_form_editor_field_settings() {
		return array(
			'error_message_setting',
			'label_setting',
			'rules_setting',
			'placeholder_setting',
			'input_class_setting',
			'css_class_setting',
			'size_setting',
			'visibility_setting',
			'conditional_logic_field_setting',
			'rules_setting',
			'description_setting',
			'vat_setting',
		);
	}

	/**
	 * Enable this field for use with conditional logic.
	 *
	 * @return bool
	 */
	public function is_conditional_logic_supported() {
		return true;
	}

	function validate( $value, $form ) {

		if ( $this->isRequired ) {
			$vat = rgar( $value, $this->id . '.1' );

			if ( empty( $vat ) && ! $this->get_input_property( $this->id . '.1', 'isHidden' )
			) {
				!
				$this->failed_validation = true;
				$this->validation_message = empty( $this->errorMessage ) ? esc_html__( 'This field is required. Please enter a VAT number.',
					'gravityforms' ) : $this->errorMessage;
			}
		}
	}

	public function get_value_submission( $field_values, $get_from_post_global_var = true ) {

		$value = parent::get_value_submission( $field_values, $get_from_post_global_var );

		return $value;
	}

	/**
	 * Define the fields inner markup.
	 *
	 * @param  array  $form  The Form Object currently being processed.
	 * @param  string|array  $value  The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param  null|array  $entry  Null or the Entry Object currently being edited.
	 *
	 * @return string
	 */
	public function get_field_input( $form, $value = '', $entry = null ) {
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();
		$is_admin        = $is_entry_detail || $is_form_editor;

		$form_id  = absint( $form['id'] );
		$id       = intval( $this->id );
		$field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";
		$form_id  = ( $is_entry_detail || $is_form_editor ) && empty( $form_id ) ? rgget( 'id' ) : $form_id;

		$disabled_text      = $is_form_editor ? "disabled='disabled'" : '';
		$class_suffix       = $is_entry_detail ? '_admin' : '';
		$required_attribute = $this->isRequired ? 'aria-required="true"' : '';

		$form_sub_label_placement  = rgar( $form, 'subLabelPlacement' );
		$field_sub_label_placement = $this->subLabelPlacement;
		$is_sub_label_above        = $field_sub_label_placement == 'above' || ( empty( $field_sub_label_placement ) && $form_sub_label_placement == 'above' );

		$vat_value             = '';//street_value
		$company_address_value = '';//street2_value
		$company_name_value    = '';//city_value
		$country_value         = '';//country_value

		if ( is_array( $value ) ) {
			$vat_value             = esc_attr( rgget( $this->id . '.1', $value ) );
			$company_address_value = esc_attr( rgget( $this->id . '.2', $value ) );
			$company_name_value    = esc_attr( rgget( $this->id . '.3', $value ) );
			$country_value         = esc_attr( rgget( $this->id . '.6', $value ) );
		}

		// Inputs.
		$vat_field_input             = GFFormsModel::get_input( $this, $this->id . '.1' );
		$company_address_field_input = GFFormsModel::get_input( $this, $this->id . '.2' );
		$company_name_field_input    = GFFormsModel::get_input( $this, $this->id . '.3' );
		$address_country_field_input = GFFormsModel::get_input( $this, $this->id . '.6' );

		// Changing css classes based on field format to ensure proper display.
		$company_name_location = 'left';
		$country_location      = 'right';

		// Labels.
		// address company address
		$company_address_sub_label = rgar( $company_address_field_input, 'customLabel' ) != ''
			? $company_address_field_input['customLabel']
			: esc_html__( 'Company Address', 'vatfieldaddon' );

		$company_address_sub_label = gf_apply_filters(
			array(
				'gform_vat_company_address',
				$form_id,
				$this->id,
			),
			$company_address_sub_label,
			$form_id
		);

		// address company name
		$company_name_sub_label = rgar( $company_name_field_input, 'customLabel' ) != ''
			? $company_name_field_input['customLabel']
			: esc_html__( 'Company Name', 'vatfieldaddon' );

		$company_name_sub_label = gf_apply_filters(
			array(
				'gform_vat_company_name',
				$form_id,
				$this->id,
			),
			$company_name_sub_label,
			$form_id
		);

		// address country
		$address_country_sub_label = rgar( $address_country_field_input, 'customLabel' ) != ''
			? $address_country_field_input['customLabel']
			: esc_html__( 'Country', 'vatfieldaddon' );

		$address_country_sub_label = gf_apply_filters(
			array(
				'gform_vat_address_country',
				$form_id,
				$this->id,
			),
			$address_country_sub_label,
			$form_id
		);

		// Placeholders.
		$vat_placeholder_attribute = $this->get_field_placeholder_attribute();

		// use label as placeholder
		$company_address_placeholder_attribute = $this->get_placeholder_html( $company_address_sub_label );
		$company_name_placeholder_attribute    = $this->get_placeholder_html( $company_name_sub_label );

		$hide_country = rgar( $address_country_field_input, 'isHidden' );

		//$country_placeholder = GFCommon::get_input_placeholder_value( $address_country_field_input );
		$country_list        = $this->get_country_dropdown( $country_value, $address_country_sub_label );

		// VAT field.
		$vat      = '';
		$tabindex = $this->get_tabindex();
		$style    = ( $is_admin && rgar( $vat_field_input, 'isHidden' ) ) ? "style='display:none;'" : '';
		if ( $is_admin || ! rgar( $vat_field_input, 'isHidden' ) ) {
			if ( $is_sub_label_above ) {
				$vat = " <span class='ginput_full{$class_suffix} vat_number' id='{$field_id}_1_container' {$style}>
                                        <label for='{$field_id}_1' id='{$field_id}_1_label'></label>
                                        <input type='text' name='input_{$id}.1' id='{$field_id}_1' value='{$vat_value}' {$tabindex} {$disabled_text} {$vat_placeholder_attribute} {$required_attribute}/>
                                    </span>";
			} else {
				$vat = " <span class='ginput_full{$class_suffix} vat_number' id='{$field_id}_1_container' {$style}>
                                        <input type='text' name='input_{$id}.1' id='{$field_id}_1' value='{$vat_value}' {$tabindex} {$disabled_text} {$vat_placeholder_attribute} {$required_attribute}/>
                                        <label for='{$field_id}_1' id='{$field_id}_1_label'></label>
                                    </span>";
			}

			if ( ! $is_admin ) {
				$vat .= wp_nonce_field( 'vat_validation', 'vat_nonce' );
			}

		}

		// Company Address field.
		$company_address = '';
		$style           = ( $is_admin && ( $this->hideAddress2 || rgar( $company_address_field_input,
					'isHidden' ) ) ) ? "style='display:none;'" : ''; // support for $this->hideAddress2 legacy property
		if ( $is_admin || ( ! $this->hideAddress2 && ! rgar( $company_address_field_input, 'isHidden' ) ) ) {
			$tabindex = $this->get_tabindex();
			if ( $is_sub_label_above ) {
				$company_address = "<span class='ginput_full{$class_suffix} vat_company_address' id='{$field_id}_2_container' {$style}>
                                        <label for='{$field_id}_2' id='{$field_id}_2_label'>{$company_address_sub_label}</label>
                                        <input type='text' name='input_{$id}.2' id='{$field_id}_2' value='{$company_address_value}' {$tabindex} {$disabled_text} {$company_address_placeholder_attribute} readonly/>
                                    </span>";
			} else {
				$company_address = "<span class='ginput_full{$class_suffix} vat_company_address' id='{$field_id}_2_container' {$style}>
                                        <input type='text' name='input_{$id}.2' id='{$field_id}_2' value='{$company_address_value}' {$tabindex} {$disabled_text} {$company_address_placeholder_attribute} readonly/>
                                        <label for='{$field_id}_2' id='{$field_id}_2_label'>{$company_address_sub_label}</label>
                                    </span>";
			}
		}

		// Company Name.
		$company_name = '';
		$tabindex     = $this->get_tabindex();
		$style        = ( $is_admin && rgar( $company_name_field_input, 'isHidden' ) ) ? "style='display:none;'" : '';
		if ( $is_admin || ! rgar( $company_name_field_input, 'isHidden' ) ) {
			if ( $is_sub_label_above ) {
				$company_name = "<span class='ginput_{$company_name_location}{$class_suffix} vat_company_name' id='{$field_id}_3_container' {$style}>
                                    <label for='{$field_id}_3' id='{$field_id}_3_label'>{$company_name_sub_label}</label>
                                    <input type='text' name='input_{$id}.3' id='{$field_id}_3' value='{$company_name_value}' {$tabindex} {$disabled_text} {$company_name_placeholder_attribute} {$required_attribute} readonly/>
                                 </span>";
			} else {
				$company_name = "<span class='ginput_{$company_name_location}{$class_suffix} vat_company_name' id='{$field_id}_3_container' {$style}>
                                    <input type='text' name='input_{$id}.3' id='{$field_id}_3' value='{$company_name_value}' {$tabindex} {$disabled_text} {$company_name_placeholder_attribute} {$required_attribute} readonly/>
                                    <label for='{$field_id}_3' id='{$field_id}_3_label'>{$company_name_sub_label}</label>
                                 </span>";
			}
		}

		if ( $is_admin || ! $hide_country ) {
			$style    = $hide_country ? "style='display:none;'" : '';
			$tabindex = $this->get_tabindex();
			if ( $is_sub_label_above ) {
				$country = "<span class='ginput_{$country_location}{$class_suffix} vat_country' id='{$field_id}_6_container' {$style}>
                                        <label for='{$field_id}_6' id='{$field_id}_6_label'>{$address_country_sub_label}</label>
                                        <select name='input_{$id}.6' id='{$field_id}_6' {$tabindex} {$disabled_text} {$required_attribute} disabled>{$country_list}</select>
                                    </span>";
			} else {
				$country = "<span class='ginput_{$country_location}{$class_suffix} vat_country' id='{$field_id}_6_container' {$style}>
                                        <select name='input_{$id}.6' id='{$field_id}_6' {$tabindex} {$disabled_text} {$required_attribute} disabled>{$country_list}</select>
                                        <label for='{$field_id}_6' id='{$field_id}_6_label'>{$address_country_sub_label}</label>
                                    </span>";
			}
		} else {
			$country = sprintf( "<input type='hidden' class='gform_hidden' name='input_%d.6' id='%s_6' value='%s'/>",
				$id, $field_id, $country_value );
		}

		$inputs = $vat . $company_address . $company_name . $country;

		$css_class = $this->get_css_class();

		return "    <div class='ginput_complex{$class_suffix} ginput_container {$css_class}' id='$field_id' >
                        {$inputs}
                    <div class='gf_clear gf_clear_complex'></div>
                </div>";
	}

	public function get_css_class() {

		$vat_field_input             = GFFormsModel::get_input( $this, $this->id . '.1' );
		$company_address_field_input = GFFormsModel::get_input( $this, $this->id . '.2' );
		$company_name_field_input    = GFFormsModel::get_input( $this, $this->id . '.3' );
		$address_country_field_input = GFFormsModel::get_input( $this, $this->id . '.6' );

		$css_class = '';
		if ( ! rgar( $vat_field_input, 'isHidden' ) ) {
			$css_class .= 'has_vat ';
		}
		if ( ! rgar( $company_address_field_input, 'isHidden' ) ) {
			$css_class .= 'has_company_address ';
		}
		if ( ! rgar( $company_name_field_input, 'isHidden' ) ) {
			$css_class .= 'has_company_name ';
		}
		if ( ! rgar( $address_country_field_input, 'isHidden' ) ) {
			$css_class .= 'has_country ';
		}

		$css_class .= 'ginput_container_vat';

		return trim( $css_class );
	}

	public function get_country_dropdown( $selected_country = '', $placeholder = '' ) {
		$str              = '';
		$selected_country = strtolower( $selected_country );
		$countries        = array_merge( array( '' ), $this->get_country_codes() );

		foreach ( $countries as $country => $code ) {
			if ( is_numeric( $code ) ) {
				$code = $country;
			}
			if ( empty( $country ) ) {
				$country = $placeholder;
			}
			$selected = strtolower( $code ) == $selected_country ? "selected='selected'" : '';
			$str      .= "<option value='" . esc_attr( $code ) . "' $selected>" . esc_html( $country ) . '</option>';
		}

		return $str;
	}

	/**
	 * Returns a list of countries and their country codes.
	 *
	 * @return array
	 * @since 2.4     Updated to use ISO 3166-1 list of countries.
	 *
	 * @since Unknown
	 */
	public function get_country_codes() {

		$codes = array(
			__( 'AUSTRIA', 'gravityforms' )        => 'AT',
			__( 'BELGIUM', 'gravityforms' )        => 'BE',
			__( 'BULGARIA', 'gravityforms' )       => 'BG',
			__( 'CROATIA', 'gravityforms' )        => 'HR',
			__( 'CYPRUS', 'gravityforms' )         => 'CY',
			__( 'CZECH REPUBLIC', 'gravityforms' ) => 'CZ',
			__( 'DENMARK', 'gravityforms' )        => 'DK',
			__( 'ESTONIA', 'gravityforms' )        => 'EE',
			__( 'FINLAND', 'gravityforms' )        => 'FI',
			__( 'FRANCE', 'gravityforms' )         => 'FR',
			__( 'GERMANY', 'gravityforms' )        => 'DE',
			__( 'HUNGARY', 'gravityforms' )        => 'HU',
			__( 'IRELAND', 'gravityforms' )        => 'IE',
			__( 'ITALY', 'gravityforms' )          => 'IT',
			__( 'LATVIA', 'gravityforms' )         => 'LV',
			__( 'LITHUANIA', 'gravityforms' )      => 'LT',
			__( 'LUXEMBOURG', 'gravityforms' )     => 'LU',
			__( 'MALTA', 'gravityforms' )          => 'MT',
			__( 'NETHERLANDS', 'gravityforms' )    => 'NL',
			__( 'POLAND', 'gravityforms' )         => 'PL',
			__( 'PORTUGAL', 'gravityforms' )       => 'PT',
			__( 'ROMANIA', 'gravityforms' )        => 'RO',
			__( 'SLOVAKIA', 'gravityforms' )       => 'SK',
			__( 'SLOVENIA', 'gravityforms' )       => 'SI',
			__( 'SPAIN', 'gravityforms' )          => 'ES',
			__( 'SWEDEN', 'gravityforms' )         => 'SE',
			__( 'UNITED KINGDOM', 'gravityforms' ) => 'GB',
		);

		return $codes;
	}

	public function get_value_entry_detail(
		$value,
		$currency = '',
		$use_text = false,
		$format = 'html',
		$media = 'screen'
	) {
		if ( is_array( $value ) ) {
			$vat_number      = trim( rgget( $this->id . '.1', $value ) );
			$company_address = trim( rgget( $this->id . '.2', $value ) );
			$company_name    = trim( rgget( $this->id . '.3', $value ) );
			$country_value   = trim( rgget( $this->id . '.6', $value ) );

			if ( $format === 'html' ) {
				$vat_number      = esc_html( $vat_number );
				$company_address = esc_html( $company_address );
				$company_name    = esc_html( $company_name );
				$country_value   = esc_html( $country_value );

				$line_break = '<br />';
			} else {
				$line_break = "\n";
			}

			/**
			 * Filters the format that the address is displayed in.
			 *
			 * @param  string           'default' The format to use. Defaults to 'default'.
			 * @param  Vat_GF_Field  $this  An instance of the Vat_GF_Field object.
			 *
			 * @since Unknown
			 *
			 */
			$response = $vat_number;
			$response .= ! empty( $address ) && ! empty( $company_address ) ? $line_break . $company_address : $company_address;
			$response .= ! empty( $address ) && ( ! empty( $company_name ) || ! empty( $state_value ) ) ? $line_break . $company_name : $company_name;
			$response .= ! empty( $address ) && ! empty( $country_value ) ? $line_break . $country_value : $country_value;

			return $response;
		} else {
			return '';
		}
	}

	/**
	 * Sanitizes the field settings.
	 *
	 * @return void
	 * @uses    GF_Field::sanitize_settings()
	 * @uses    GF_Field_Phone::get_phone_format()
	 * @uses    GF_Field_Phone::$phoneFormat
	 *
	 * @since  Unknown
	 * @access public
	 *
	 * @used-by GFFormDetail::add_field()
	 * @used-by GFFormsModel::sanitize_settings()
	 */
	public function sanitize_settings() {
		parent::sanitize_settings();
	}

	private function get_placeholder_html( $placeholder_value = '' ) {
		return sprintf( "placeholder='%s'", esc_attr( $placeholder_value ) );
	}
}

GF_Fields::register( new Vat_GF_Field() );