<?php
/**
 * Plugin Name: Mobilia Helper
 * Plugin URI: http://roadthemes.com/
 * Description: The helper plugin for RoadThemes themes.
 * Version: 1.0.0
 * Author: RoadThemes
 * Author URI: http://roadthemes.com/
 * Text Domain: mobilia
 * License: GPL/GNU.
 /*  Copyright 2014  RoadThemes  (email : support@roadthemes.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//Add less compiler
function compileLessFile($input, $output, $params) {
   require_once( plugin_dir_path( __FILE__ ).'less/lessc.inc.php' );
   
	$less = new lessc;
	$less->setVariables($params);
	
    // input and output location
    $inputFile = get_template_directory().'/less/'.$input;
    $outputFile = get_template_directory().'/css/'.$output;

    try {
		$less->compileFile($inputFile, $outputFile);
	} catch (Exception $ex) {
		echo "lessphp fatal error: ".$ex->getMessage();
	}
}
function compileChildLessFile($input, $output, $params) {
	require_once( plugin_dir_path( __FILE__ ).'less/lessc.inc.php' );
	$less = new lessc;
	$less->setVariables($params);
	
    // input and output location
    $inputFile = get_stylesheet_directory().'/less/'.$input;
    $outputFile = get_stylesheet_directory().'/css/'.$output;

    try {
		$less->compileFile($inputFile, $outputFile);
	} catch (Exception $ex) {
		echo "lessphp fatal error: ".$ex->getMessage();
	}
}
//Shortcodes
add_shortcode( 'roadlogo', 'mobilia_logo_shortcode' );
add_shortcode( 'roadmainmenu', 'mobilia_mainmenu_shortcode' );
add_shortcode( 'roadcategoriesmenu', 'mobilia_roadcategoriesmenu_shortcode' );
add_shortcode( 'roadlangswitch', 'mobilia_roadlangswitch_shortcode' );
add_shortcode( 'roadsocialicons', 'mobilia_roadsocialicons_shortcode' );
add_shortcode( 'roadminicart', 'mobilia_roadminicart_shortcode' );
add_shortcode( 'roadproductssearch', 'mobilia_roadproductssearch_shortcode' );
add_shortcode( 'roadcopyright', 'mobilia_roadcopyright_shortcode' );
add_shortcode( 'ourbrands', 'mobilia_brands_shortcode' );
add_shortcode( 'mobilia_counter', 'mobilia_counter_shortcode' );
add_shortcode( 'popular_categories', 'mobilia_popular_categories_shortcode' );
add_shortcode( 'categoriescarousel', 'mobilia_categoriescarousel_shortcode' );
add_shortcode( 'latestposts', 'mobilia_latestposts_shortcode' );
add_shortcode( 'mobilia_map', 'mobilia_contact_map' );