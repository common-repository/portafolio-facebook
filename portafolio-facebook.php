<?php
/*
Plugin Name: Portafolio Facebook
Plugin URI: http://www.magiabinaria.com
Description: This Plugin Allow to show all your Facebook Album with Portfolio tag at your site in Real-time. / Este Plugin permite mostrar todas los albums y fotos de tu fanpage de Facebook en tu sitio web en Tiempo Real filtrando solo las de tu portafolio. Para mas detalles visita <a href="http://www.magiabinaria.com">Albums & Fotos de Facebook / Soporte y Ayuda</a>
Author: magiabinaria
Version: 1.2
Author URI: http://profiles.wordpress.org/magiabinaria/
*/
/*  Copyright 2015  Magia Binaria  (email : admin@magiabinaria.com)

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

/**
 * Add stylesheet to the plugin
*/
add_action( 'wp_enqueue_scripts', 'portafolio_facebook_stylesheet' );
function portafolio_facebook_stylesheet() {
wp_enqueue_style( 'prefix-style', plugins_url('inc/prettyPhoto.css', __FILE__) );
}

/**
 * Add js file to the plugin
*/
add_action( 'wp_enqueue_scripts', 'portafolio_facebook_scripts');
function portafolio_facebook_scripts()
{
wp_register_script( 'pf-custom-script', plugins_url( 'inc/jquery.prettyPhoto.js', __FILE__ ), array( 'jquery' ) );	
wp_enqueue_script( 'pf-custom-script' );
}

/**
 * Add js2 file to the plugin
*/
add_action( 'wp_enqueue_scripts', 'portafolio_facebook_scripts2');
function portafolio_facebook_scripts2()
{
wp_register_script( 'pf-custom-script2', plugins_url( 'inc/funcion.js', __FILE__ ), array( 'jquery' ) );	
wp_enqueue_script( 'pf-custom-script2' );
}


function portafolio_facebook_showPhotos(){
	$url=get_option("fb_page_link");
	$fbOwnerId=get_option("fb_owner_id");
 	//require 'fb-sdk/src/facebook.php';
	if (!class_exists('FacebookApiException')) {
include_once('fb-sdk/src/facebook.php');
}
	$facebook = new Facebook(array(
	//EDIT YOUR FACEBOOK APP ID AND SECRET HERE
	  'appId'  => '1444570185770872',
	  'secret' => 'ecd52013415003d97e3774f94de7a0fb',
	  'cookie' => true,
	));
	
	isset( $_REQUEST['action'] ) ? $action = $_REQUEST['action'] : $action = "";
	if( $action == ''){
	$fql    =   "SELECT aid, cover_pid, name FROM album WHERE owner=$fbOwnerId AND strpos(lower(name),'portafolio') >=0";
	$param  =   array(
	 'method'    => 'fql.query',
	 'query'     => $fql,
	 'callback'  => ''
	);
	$fqlResult   =   $facebook->api($param);
	foreach( $fqlResult as $keys => $values ){
		$fql2    =   "select src from photo where pid = '" . $values['cover_pid'] . "'";
		$param2  =   array(
		 'method'    => 'fql.query',
		 'query'     => $fql2,
		 'callback'  => ''
		);
		$fqlResult2   =   $facebook->api($param2);
		foreach( $fqlResult2 as $keys2 => $values2){
			$album_cover = $values2['src'];
		}
		echo "<div class='losalbums'>";
		//echo "<a href='".$url."?action=obtener_fotos&aid=" . $values['aid'] . "&album_name=" . $values['name'] . "'>";
		echo "<a href='?action=obtener_fotos&aid=" . $values['aid'] . "&album_name=" . $values['name'] . "'>";
		echo "<img src='$album_cover'>";
		echo "</a><br />";
		echo $values['name'];
		echo "</div>";
	}
}

if( $action == 'obtener_fotos'){
	isset( $_GET['album_name'] ) ? $album_name = $_GET['album_name'] : $album_name = "";
	echo "<div><a href='".$url."'>Volver</a> | Nombre: <b>" . $album_name . "</b></div>";
	//echo "<div style='padding: 0px; '> URL guardada desde el admin: <b>" . $url . "</b></div>";
	$fql    =   "SELECT pid, src, src_small, src_big, caption FROM photo WHERE aid = '" . $_REQUEST['aid'] ."'  ORDER BY created DESC";
	$param  =   array(
	 'method'    => 'fql.query',
	 'query'     => $fql,
	 'callback'  => ''
	);
	$fqlResult   =   $facebook->api($param);
	
	echo "<div id='portafb'><ul class='gallery clearfix'>";	
	foreach( $fqlResult as $keys => $values ){
		
		if( $values['caption'] == '' ){ 
			$caption = "";
		}else{
		
			$caption = $values['caption'];
		}	
			echo "<li><a rel='prettyPhoto[gallery2]' href=\"" . $values['src_big'] . "\" title=\"" . $caption . "\">";
			echo "<img src='" . $values['src'] . "' class='lasfotos' />";
			echo "</a></li>"; 
	}
	echo "</ul></div>";
}

}

function pf_setting_menu() {
	add_options_page('Opciones para Portafolio Facebook', 'Portafolio Facebook', 8, 'PortafolioFacebook', 'portafolio_facebook_options_page');
}

function portafolio_facebook_options_page() {
	echo '<div class="wrap">';
	echo '<h2>Portafolio Facebook ' . __('Options', 'fbfotos') . '</h2>';
	echo '<div> Si necesitas ayuda para configurar correctamente el plugin <b>Portafolio Facebook</b> ve los links de ayuda.</div>';
	echo '<form method="post" action="options.php">';
	wp_nonce_field('update-options');
	echo '<table class="form-table" style="width:900px;">';
	echo '<tr valign="top">';
	echo '<th scope="row">' . __('Facebook ID de Fanpage :', 'fbfotos') . '</th>';
	echo '<td><input type="text" name="fb_owner_id" value="' . get_option('fb_owner_id') . '" /> <b> (ejemplo : 100002245703208)</b> <br /> Puedes encontrar tu ID en este link: <a href="http://findmyfacebookid.com/" target="_blank">http://findmyfacebookid.com/</a></td>';
	echo '</tr>';
	echo '<tr valign="top">';
	echo '<th scope="row">' . __('URL donde se mostrara la galeria :', 'fbfotos') . '</th>';
	echo '<td><input type="text" name="fb_page_link" style="width:300px;"  value="' . get_option('fb_page_link') . '" /><b>(ejemplo : http://www.magiabinaria.com/portafolio)</b></b> <br /> Sitio Web donde se mostrara la galeria.</td>';
	echo '</tr>';
	echo '</table>';
	echo '<p class="submit">';	
	echo '<input type="submit" class="button-primary" value="' . __('Guardar Cambios') . '" />';
	echo '</p>';
  	settings_fields('fbOwnerID');
    echo '</form>';
	echo '</div>';
	echo '<h2>HELP</h2>';
	echo '<div>IMPORTANT: For the Use of this plugin , create the new page and place the simple code as following: [PORTAFOLIOFB] </div>';
	echo '<div> For english version please <b>Contact us</b> in twitter :) </div>';
	echo '<h2>Ayuda</h2>';
	echo '<div> Para mostrar la galeria ingresa el siguiente Shortcode en una pagina nueva: [PORTAFOLIOFB] </div>';
	echo '<div> Version <b>PRO</b> Gratis</div>';
	echo '<p>Nos puedes contactar en:- <a href="http://www.magiabinaria.com/?ref=plugin" target="_blank">http://www.magiabinaria.com</a> para dudas o ayuda.<br />
		<br /> <p >o tambien</p> <br />Email:<a href="mailto:info@magiabinaria.com">info@magiabinaria.com</a> <br /> Nos pondremos en contacto.';
	echo '</p>';  
}

function portafolio_facebook_register_settings() {
	register_setting('fbOwnerID', 'fb_owner_id');
	register_setting('fbOwnerID', 'fb_page_link');
	
	}
$plugin_dir = basename(dirname(__FILE__));
add_option("fb_owner_id");
add_option("fb_page_link");



if(is_admin()){
	add_action('admin_menu', 'pf_setting_menu');
	add_action('admin_init', 'portafolio_facebook_register_settings');
}

function portafolio_facebook_func( $atts ){
 return portafolio_facebook_showPhotos();
}
add_shortcode('PORTAFOLIOFB', 'portafolio_facebook_func');

function portafolio_facebook_action_links($links, $file) {
    static $this_plugin;
     if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }
    if ($file == $this_plugin) {
        // the anchor tag and href to the URL we want. For a "Settings" link, this needs to be the url of your settings page
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=PortafolioFacebook">Configurar</a>';
        // add the link to the list
        array_unshift($links, $settings_link);
    }
     return $links;
}
add_filter('plugin_action_links', 'portafolio_facebook_action_links', 10, 2);
?>