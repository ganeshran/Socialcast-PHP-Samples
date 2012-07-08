<?php
/*
 * +--------------------------------------------------------------------------+
 * | Copyright (c) 2011 Socialcast, Inc.                                      |
 * +--------------------------------------------------------------------------+
 * | This program is free software; you can redistribute it and/or modify     |
 * | it under the terms of the GNU General Public License as published by     |
 * | the Free Software Foundation; either version 2 of the License, or        |
 * | (at your option) any later version.                                      |
 * |                                                                          |
 * | This program is distributed in the hope that it will be useful,          |
 * | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
 * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
 * | GNU General Public License for more details.                             |
 * |                                                                          |
 * | You should have received a copy of the GNU General Public License        |
 * | along with this program; if not, write to the Free Software              |
 * | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA |
 * +--------------------------------------------------------------------------+
 *
 * Ganesh Ranganathan - Forked this plugin in an attempt to make it work with WP 3.4.1
 * Issues Encountered
 * has_cap is deprected : Using roles and capabilities instead
 */

/**
 * Plugin Name: Socialcast Reach Extensions
 * Plugin URI: http://developers.socialcast.com/business-systems/wordpress
 * Description: The REACH plugin gives you the ability to add short codes for any Reach extension, such as Like, Recommend, Discussion Box, Stream and Trends to your WordPress page or blog
 * entry. Supports theming.
 *
 * Version: 1.0
 *
 * Author: Monica Wilkinson
 * Author URI: http://developers.socialcast.com/author/ciberch/
 */

/**
 * Returns major.minor WordPress version.
 */
function sc_reach_get_wp_version() {
  return (float) substr(get_bloginfo('version'), 0, 3);
}

function sc_add_author_stream($email, $style='width:300px;height:400px') { 
  return get_div_email($email, 'profile_container_id', $style, get_option('sc_profile_token'));
}

function get_div_email($email, $id, $style, $token) {
	$socialcast_url = get_option('sc_host');
	if ($id != '' && $token != '') {
		return '<div id="' . $id . '" style="' . $style .
		'"></div><script type="text/javascript">_reach.push({container: "' . $id . '", domain: "https://' 
		. $socialcast_url . '", token: "' . $token . '", email:"'. $email . '"});</script>';
	} else {
		return '';
	}
}

function sc_reach_content_handle($content, $sidebar = false) {
	
	switch(get_option('sc_show_button'))
	{
		case 'Dont Show':
			break;
		case 'Top':
			$content =  sc_add_button() . $content;
			break;
		case 'Bottom':
			$content = $content . sc_add_button();
			break;
	}
	
	if (get_option('sc_use_microdata') == 'true') {
		$purl = get_permalink();
   		$content = "<div itemscope=itemscope itemtype=\"http://ogp.me/ns#Blog\"><a itemprop='url' href='" . $purl . "' /></a>" . $content . "</div>";
	}

	return $content;
}

function get_div($id, $style, $token) {
	$socialcast_url = get_option('sc_host');
	if ($id != '' && $token != '') {
		return '<div id="' . $id . '" style="' . $style .
		'"></div><script type="text/javascript">_reach.push({container: "' . $id . '", domain: "https://' 
		. $socialcast_url . '", token: "' . $token . '"});</script>';
	} else {
		return '';
	}
}

/* Name: Ganesh Ranganathan 
   Date: 7th July 2012
This function has been added to generate the div from the shortcut
which can be added to any text widget or in the post itself. It includes
an additional parameter - display which lets users insert shortcodes without
knowing the token as long as they are specified in the Plugin options screen.
Please make sure they are do_shortcode is called in your theme for widget text if 
you want to include this in a widget */
function get_shortcode_div($id,$style,$token,$display){
 $tokenInOptions ='';
  if($display != '')
  {
		switch($display)
		{
			case 'button':
				$tokenInOptions=get_option('sc_button_token');
				break;
			case 'discussion':
				$tokenInOptions= get_option('sc_discussion_token');
				break;
			case 'profile':
			    $tokenInOptions = get_option('sc_profile_token');
				break;
			case 'trends':
				$tokenInOptions = get_option('sc_trends_token');
				break;
				
		}
		if($tokenInOptions != '')
		   return get_div($id,$style,$tokenInOptions);
	
	
  }
  return get_div($id,$style,$token);
}

function add_reach($atts) {
	extract( shortcode_atts( array(
			'id' => 'reach_container_id',
			'style' => '',
			'token' => '',
			'display' => ''
		), $atts ) );

  return get_shortcode_div($id, $style, $token,$display);
}

function reach_init_method() {

  if (sc_reach_get_wp_version() >= 2.7) {
    if (is_admin ()) {
      add_action('admin_init', 'sc_reach_register_settings');
    }
  }
  add_filter('the_content', 'sc_reach_content_handle');
  add_filter('admin_menu', 'sc_reach_admin_menu');
  
  add_option('sc_host', '');
  add_option('sc_button_token', '');
  add_option('sc_discussion_token', '');
  add_option('sc_profile_token', '');
  add_option('sc_use_microdata', 'true');
  add_option('sc_trends_token', '');
  add_option('sc_enableShortcode','1');
  add_option('sc_show_button','Dont Show');
  add_action('wp_head', 'sc_reach_header_meta');
  add_action('wp_footer', 'sc_reach_add_js');
  if(get_option('sc_enableShortcode'))
  add_shortcode( 'reach', 'add_reach' );
  else
  remove_shortcode('reach');
  
//echo "Get option" . get_option('sc_show_button');

}

function sc_reach_header_meta() {
  echo '<script type="text/javascript">var _reach = _reach || [];</script>';
}

function sc_reach_register_settings() {
  register_setting('sc_reach', 'sc_host');
  register_setting('sc_reach', 'sc_button_token');
  register_setting('sc_reach', 'sc_discussion_token');
  register_setting('sc_reach', 'sc_trends_token');
  register_setting('sc_reach', 'sc_profile_token');
  register_setting('sc_reach', 'sc_use_microdata');
  register_setting('sc_reach', 'sc_enableShortcode');
  register_setting('sc_reach','sc_show_button');
}

function sc_add_button($style='width:300px;height:30px') {
	return get_div('like_container_id', $style, get_option('sc_button_token'));
}

function sc_add_discussion($style='width:300px;height:400px') {
	return get_div('discussion_container_id', $style, get_option('sc_discussion_token'));
}

function sc_add_token($style='width:300px;height:400px'){
	return get_div('trends_container_id',$style,get_option('sc_trends_token'));
}

function sc_reach_admin_menu() {
  add_options_page('REACH Plugin Options', 'Socialcast REACH',  'activate_plugins', __FILE__, 'sc_reach_options');
}

function sc_reach_options() {
?>

  <div class="wrap">
    <h2>Reach Extensions by <a href="http://www.socialcast.com" target="_blank">Socialcast</a></h2>

    <form method="post" action="options.php">
    <?php
	
    if (sc_reach_get_wp_version() < 2.7) {
      wp_nonce_field('update-options');
    } else {
      settings_fields('sc_reach');
    }
    ?>

      <h2>Instructions</h2>
      <p>If you are not logged in to Socialcast. Please do so with a user that has administrative credentials.
        Once there either Create an HTML Extension or select one from the list. For more information please visit the <a href="http://integrate.socialcast.com/business-systems/wordpress/">plugin page</a>.
      </p>
      <h3>Socialcast Community</h3>
		<table>
			<tr><td>https://</td><td><input style="width:400px" type="text" name="sc_host" value="<?php echo get_option('sc_host'); ?>" /></td></tr>
			<tr><td>Add HTML Microdata ?</td><td><input style="width:400px" type="text" name="sc_use_microdata" value="<?php echo get_option('sc_use_microdata'); ?>" />Type 'true' to use</td></tr>
			<tr><td>Button Token:</td><td><input style="width:400px" type="text" name="sc_button_token" value="<?php echo get_option('sc_button_token'); ?>" />Function: sc_add_button()</td></tr>
			<tr><td>Discussion Token:</td><td><input style="width:400px" type="text" name="sc_discussion_token" value="<?php echo get_option('sc_discussion_token'); ?>" />Function sc_add_discussion()</td></tr>
            <tr><td>Profile Token:</td><td><input style="width:400px" type="text" name="sc_profile_token" value="<?php echo get_option('sc_profile_token'); ?>" />Function sc_add_author_stream()</td></tr>
			<tr><td>Trends Token:</td><td><input style="width:400px" type="text" name="sc_trends_token" value="<?php echo get_option('sc_trends_token'); ?>" />Function sc_add_trends()</td></tr>
			<tr><td>Enable Shortcode: </td><td><input name="sc_enableShortcode" type="checkbox" value="1" <?php checked( '1', get_option( 'sc_enableShortcode' ) ); ?> /></td></tr>
			<tr><td>Show Button:</td> <td>
			<select style="width:400px" name="sc_show_button" id="sc_show_button">
<option value="Top" <?php if (get_option('sc_show_button')=='Top') echo 'selected="selected"';?>>Top</options>
<option value="Bottom" <?php if (get_option('sc_show_button')=='Bottom') echo 'selected="selected"';?>>Bottom</options>
<option value="Dont Show" <?php if (get_option('sc_show_button')=='Dont Show') echo 'selected="selected"';?>>Dont Show</options>
</select>Make sure the button token is set for this to work</td></tr>
		</table>
    <?php if (sc_reach_get_wp_version() < 2.7) : ?>
      <input type="hidden" name="action" value="update" />
      <input type="hidden" name="page_options" value="sc_host" />
    <?php endif; ?>
      <p class="submit">
        <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
      </p>
    </form>
    <iframe width="100%" height="600px" src="http://developers.socialcast.com/reach/setup-a-reach-extension/">
    </iframe>
  </div>

<?php
    }

    function sc_reach_add_js() {
?>
      <script type="text/javascript">
        (function(){
          var e=document.createElement('script');
          e.type='text/javascript';
          e.async = true;
          e.src= document.location.protocol + '//<?php echo get_option('sc_host') ?>/services/reach/extension.js';
          var s = document.getElementsByTagName('script')[0];
          s.parentNode.insertBefore(e, s);
        })();
      </script>
<?php
    }

    reach_init_method();
?>