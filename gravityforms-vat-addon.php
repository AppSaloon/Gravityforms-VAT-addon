<?php
/*
Plugin Name: Gravity Forms Vat Field Add-On
Plugin URI: https://www.appsaloon.be
Description: A GF vat add-on to check the VAT number on a VAT service
Version: 1.0.0
Author: AppSaloon
Author URI: https://www.appsaloon.be
Text Domain: vatfieldaddon
Domain Path: /languages

------------------------------------------------------------------------
Copyright 2012-2016 Rocketgenius Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

/**
 * https://github.com/DragonBe/vies
 * https://vatlayer.com
 * https://trello.com/c/nn9gNp4s/8-klant-worden-form
 * https://javascript.info/fetch
 */

define( 'GF_VAT_FIELD_ADDON_VERSION', '1.0.0' );

require_once __DIR__ . '/vendor/autoload.php';

add_action( 'gform_loaded', array( 'GF_Vat_Field_AddOn_Bootstrap', 'load' ), 5 );

class GF_Vat_Field_AddOn_Bootstrap {

	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		require_once( 'class-gfvatfieldaddon.php' );

		GFAddOn::register( 'GFVatFieldAddOn' );
	}

}