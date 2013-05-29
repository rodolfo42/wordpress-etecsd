<?php
/*
Plugin Name: Super RSS Reader
Plugin URI: http://www.aakashweb.com/wordpress-plugins/super-rss-reader/
Author URI: http://www.aakashweb.com/
Description: Super RSS Reader is jQuery based RSS reader widget, which displays the RSS feeds in the widget in an attractive way. It uses the jQuery easy ticker plugin to add a news ticker like effect to the RSS feeds. Multiple RSS feeds can be added for a single widget and they get seperated in tabs. <a href="http://www.youtube.com/watch?v=02aOG_-98Tg" target="_blank" title="Super RSS Reader demo video">Check out the demo video</a>.
Author: Aakash Chakravarthy
Version: 2.4
*/

if(!defined('WP_CONTENT_URL')) {
	$srr_url = get_option('siteurl') . '/wp-content/plugins/' . plugin_basename(dirname(__FILE__)).'/';
}else{
	$srr_url = WP_CONTENT_URL . '/plugins/' . plugin_basename(dirname(__FILE__)) . '/';
}

define('SRR_VERSION', '2.4');
define('SRR_AUTHOR', 'Aakash Chakravarthy');
define('SRR_URL', $srr_url);

## Include the required scripts
function srr_public_scripts(){
	// jQuery
    wp_enqueue_script('jquery');
	
	// Super RSS Reader JS and CSS
	wp_register_script('super-rss-reader-js', SRR_URL . 'public/srr-js.js');
	wp_enqueue_script(array('jquery', 'super-rss-reader-js'));
}
add_action('wp_enqueue_scripts', 'srr_public_scripts');

## Include the required styles
function srr_public_styles(){
	wp_register_style('super-rss-reader-css', SRR_URL . 'public/srr-css.css');
	wp_enqueue_style('super-rss-reader-css');
}
add_action('wp_enqueue_scripts', 'srr_public_styles');

## Default color styles
$srr_color_styles = array(
	'No style' => 'none',
	'Grey' => 'grey',
	'Dark' => 'dark',
	'Orange' => 'orange',
	'Simple modern' => 'smodern'
);

## The main RSS Parser
function srr_rss_parser($instance){
	
	$urls = stripslashes($instance['urls']);
	$count = intval($instance['count']);
	$show_date = intval($instance['show_date']);
	$show_desc = intval($instance['show_desc']);
	$show_author = intval($instance['show_author']);
	$show_thumb = stripslashes($instance['show_thumb']);
	$open_newtab = intval($instance['open_newtab']);
	$strip_desc = intval($instance['strip_desc']);
	$strip_title = intval($instance['strip_title']);
	$read_more = htmlspecialchars($instance['read_more']);
	$color_style = stripslashes($instance['color_style']);
	$enable_ticker = intval($instance['enable_ticker']);
	$visible_items = intval($instance['visible_items']);
	$ticker_speed = intval($instance['ticker_speed']) * 1000;
	
	if(empty($urls)){
		return '';
	}
	
	$rand = array();
	$url = explode(',', $urls);
	$ucount = count($url);
	
	// Generate the Tabs
	if($ucount > 1){
		echo '<ul class="srr-tab-wrap srr-tab-style-' . $color_style . ' srr-clearfix">';
		for($i=0; $i<$ucount; $i++){
			// Get the Feed URL
			$feedUrl = trim($url[$i]);
			$rss = fetch_feed($feedUrl);
			$rand[$i] = rand(0, 999);
			
			if (!is_wp_error($rss)){
				$rss_title = esc_attr(strip_tags($rss->get_title()));
				echo '<li data-tab="srr-tab-' . $rand[$i] . '">' . $rss_title . '</li>';
			}else{
				echo '<li data-tab="srr-tab-' . $rand[$i] . '">Error</li>';
			}
			
		}
		echo '</ul>';
	}
	
	for($i=0; $i<$ucount; $i++){
		// Get the Feed URL
		$feedUrl = trim($url[$i]);
		if(isset($url[$i])){
			$rss = fetch_feed($feedUrl);
		}else{
			return '';
		}
		
		// Check for feed errors
		if (!is_wp_error( $rss ) ){
			$maxitems = $rss->get_item_quantity($count);
			$rss_items = $rss->get_items(0, $maxitems); 
			$rss_title = esc_attr(strip_tags($rss->get_title()));
			$rss_desc = esc_attr(strip_tags($rss->get_description()));
		}else{
			echo '<div class="srr-wrap srr-style-' . $color_style .'" data-id="srr-tab-' . $rand[$i] . '"><p>RSS Error: ' . $rss->get_error_message() . '</p></div>';
			continue;
		}
		
		$randAttr = isset($rand[$i]) ? ' data-id="srr-tab-' . $rand[$i] . '" ' : '';
		
		// Outer Wrap start
		echo '<div class="srr-wrap ' . (($enable_ticker == 1 ) ? 'srr-vticker' : '' ) . ' srr-style-' . $color_style . '" data-visible="' . $visible_items . '" data-speed="' . $ticker_speed . '"' . $randAttr . '><div>';
		
		// Check feed items
		if ($maxitems == 0){
			echo '<div>No items.</div>';
		}else{
			$j=1;
			// Loop through each feed item
			foreach ($rss_items as $item){
				// Get the link
				$link = $item->get_link();
				while ( stristr($link, 'http') != $link ){ $link = substr($link, 1); }
				$link = esc_url(strip_tags($link));
				
				// Get the item title
				$title = esc_attr(strip_tags($item->get_title()));
				if ( empty($title) )
					$title = __('No Title');
				
				if($strip_title != 0){
					$titleLen = strlen($title);
					$title = wp_html_excerpt( $title, $strip_title );
					$title = ($titleLen > $strip_title) ? $title . ' ...' : $title;
				}
				
				// Get the date
				$date = $item->get_date('j F Y');
				
				// Get thumbnail if present @since v2.2
				$thumb = '';
				if ($show_thumb == 1 && $enclosure = $item->get_enclosure()){
					$thumburl = $enclosure->get_thumbnail();
					if(!empty($thumburl))
					$thumb = '<img src="' . $thumburl . '" alt="' . $title . '" class="srr-thumb" align="left"/>';
				}
				
				// Get the description
				$desc = str_replace( array("\n", "\r"), ' ', esc_attr( strip_tags( @html_entity_decode( $item->get_description(), ENT_QUOTES, get_option('blog_charset') ) ) ) );
				if($strip_desc != 0){
					$desc = wp_html_excerpt( $desc, $strip_desc );
					$rmore = (!empty($read_more)) ?  '<a href="' . $link . '" title="Read more">' . $read_more . '</a>' : '';
					
					if ( '[...]' == substr( $desc, -5 ) )
						$desc = substr( $desc, 0, -5 );
					elseif ( '[&hellip;]' != substr( $desc, -10 ) )
						$desc .= '';
						
					$desc = esc_html( $desc );
				}
				$desc = $thumb . $desc . ' ' . $rmore;
				
				// Get the author
				$author = $item->get_author();
				if ( is_object($author) ) {
					$author = $author->get_name();
					$author = esc_html(strip_tags($author));
				}
		
				// Open links in new tab
				$newtab = ($open_newtab) ? ' target="_blank"' : '';
				
				echo "\n\n\t";
				
				// Display the feed items
				echo '<div class="srr-item ' . (($j%2 == 0) ? 'even' : 'odd') . '">';
				echo '<div class="srr-title"><a href="' . $link . '"' . $newtab . ' title="Posted on ' . $date . '">' . $title . '</a></div>';
				echo '<div class="srr-meta">';
				
				if($show_date && !empty($date))
					echo '<time class="srr-date">' . $date . '</time>';
					
				if($show_author && !empty($author))
					echo ' - <cite class="srr-author">' . $author . '</cite>';
				
				echo '</div>';
				
				if($show_desc)
					echo '<p class="srr-summary srr-clearfix">' . $desc . '</p>';
					
				echo '</div>';
				// End display
				
				$j++;
			}
		}
		
		// Outer wrap end
		echo "\n\n</div>
		</div> \n\n" ;
		
	}
}

class super_rss_reader_widget extends WP_Widget{
	## Initialize
	function super_rss_reader_widget(){
		$widget_ops = array(
			'classname' => 'widget_super_rss_reader',
			'description' => "Enhanced RSS feed reader widget with advanced features."
		);
		
		$control_ops = array('width' => 430, 'height' => 500);
		parent::WP_Widget('super_rss_reader', 'Super RSS Reader', $widget_ops, $control_ops);
	}
	
	## Display the Widget
	function widget($args, $instance){
		extract($args);
		if(empty($instance['title'])){
			$title = '';
		}else{
			$title = $before_title . apply_filters('widget_title', $instance['title'], $instance, $this->id_base) . $after_title;
		}
		
		echo $before_widget . $title;
		echo "\n" . '
		<!-- Start - Super RSS Reader v' . SRR_VERSION . '-->
		<div class="super-rss-reader-widget">' . "\n";
		
		srr_rss_parser($instance);
		
		echo "\n" . '</div>
		<!-- End - Super RSS Reader -->
		' . "\n";
		echo $after_widget;
	}
	
	## Save settings
	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = stripslashes($new_instance['title']);
		$instance['urls'] = stripslashes($new_instance['urls']);
		
		$instance['count'] = intval($new_instance['count']);
		$instance['show_date'] = intval($new_instance['show_date']);
		$instance['show_desc'] = intval($new_instance['show_desc']);
		$instance['show_author'] = intval($new_instance['show_author']);
		$instance['show_thumb'] = stripslashes($new_instance['show_thumb']);
		$instance['open_newtab'] = intval($new_instance['open_newtab']);
		$instance['strip_desc'] = intval($new_instance['strip_desc']);
		$instance['strip_title'] = intval($new_instance['strip_title']);
		$instance['read_more'] = stripslashes($new_instance['read_more']);
		
		$instance['color_style'] = stripslashes($new_instance['color_style']);
		$instance['enable_ticker'] = intval($new_instance['enable_ticker']);
		$instance['visible_items'] = intval($new_instance['visible_items']);
		$instance['ticker_speed'] = intval($new_instance['ticker_speed']);
		
		return $instance;
	}
	
	## HJA Widget form
	function form($instance){
		global $srr_color_styles;
		
		$instance = wp_parse_args( (array) $instance, array(
			'title' => '', 'urls' => '', 'count' => 5,
			'show_date' => 0, 'show_desc' => 1, 'show_author' => 0, 'show_thumb' => 1, 
			'open_newtab' => 1, 'strip_desc' => 100, 'read_more' => '[...]',
			'color_style' => 'none', 'enable_ticker' => 1, 'visible_items' => 5, 
			'strip_title' => 0, 'ticker_speed' => 2,
		));
		
		$title = htmlspecialchars($instance['title']);
		$urls = htmlspecialchars($instance['urls']);
		
		$count = intval($instance['count']);
		$show_date = intval($instance['show_date']);
		$show_desc = intval($instance['show_desc']);
		$show_author = intval($instance['show_author']);
		$show_thumb = intval($instance['show_thumb']);
		$open_newtab = intval($instance['open_newtab']);
		$strip_desc = intval($instance['strip_desc']);
		$strip_title = intval($instance['strip_title']);
		$read_more = htmlspecialchars($instance['read_more']);
		
		$color_style = stripslashes($instance['color_style']);
		$enable_ticker = intval($instance['enable_ticker']);
		$visible_items = intval($instance['visible_items']);
		$ticker_speed = intval($instance['ticker_speed']);
		
		?>
		<div class="srr_settings">
		<table width="100%" height="72" border="0">
		<tr>
		  <td width="13%" height="33"><label for="<?php echo $this->get_field_id('title'); ?>">Title: </label></td>
		  <td width="87%"><input id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" class="widefat"/></td>
		</tr>
		<tr>
			<td><label for="<?php echo $this->get_field_id('urls'); ?>">URLs: </label></td>
			<td><input id="<?php echo $this->get_field_id('urls');?>" name="<?php echo $this->get_field_name('urls'); ?>" type="text" value="<?php echo $urls; ?>" class="widefat"/>
			<small class="srr_smalltext">Can enter multiple RSS/atom feed URLs seperated by a comma.</small>
			</td>
		</tr>
		</table>
		</div>
		
		<div class="srr_settings">
		<h4>Settings</h4>
		<table width="100%" border="0">
		  <tr>
			<td width="7%" height="28"><input id="<?php echo $this->get_field_id('show_desc'); ?>" type="checkbox"  name="<?php echo $this->get_field_name('show_desc'); ?>" value="1" <?php echo $show_desc == "1" ? 'checked="checked"' : ""; ?> /></td>
			<td width="40%"><label for="<?php echo $this->get_field_id('show_desc'); ?>">Show Description</label></td>
			<td width="28%"><label for="<?php echo $this->get_field_id('count');?>">Count</label></td>
			<td width="25%"><input id="<?php echo $this->get_field_id('count');?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo $count; ?>" class="widefat" title="No of feed items to parse"/></td>
		  </tr>
		  <tr>
			<td height="32"><input id="<?php echo $this->get_field_id('show_date'); ?>" type="checkbox"  name="<?php echo $this->get_field_name('show_date'); ?>" value="1" <?php echo $show_date == "1" ? 'checked="checked"' : ""; ?> /></td>
			<td><label for="<?php echo $this->get_field_id('show_date'); ?>">Show Date</label></td>
			<td><label for="<?php echo $this->get_field_id('strip_desc');?>">Strip description</label></td>
			<td><input id="<?php echo $this->get_field_id('strip_desc');?>" name="<?php echo $this->get_field_name('strip_desc'); ?>" type="text" value="<?php echo $strip_desc; ?>" class="widefat" title="The number of charaters to be displayed. Use 0 to disable stripping"/>			</td>
		  </tr>
		  <tr>
			<td height="29"><input id="<?php echo $this->get_field_id('show_author'); ?>" type="checkbox"  name="<?php echo $this->get_field_name('show_author'); ?>" value="1" <?php echo $show_author == "1" ? 'checked="checked"' : ""; ?> /></td>
			<td><label for="<?php echo $this->get_field_id('show_author'); ?>">Show Author</label></td>
			<td><label for="<?php echo $this->get_field_id('read_more'); ?>">Read more text</label></td>
			<td><input id="<?php echo $this->get_field_name('read_more'); ?>" name="<?php echo $this->get_field_name('read_more'); ?>" type="text" value="<?php echo $read_more; ?>" class="widefat" title="Leave blank to hide read more text"/></td>
		  </tr>
		  <tr>
		    <td height="29"><input id="<?php echo $this->get_field_id('open_newtab'); ?>" type="checkbox"  name="<?php echo $this->get_field_name('open_newtab'); ?>" value="1" <?php echo $open_newtab == "1" ? 'checked="checked"' : ""; ?> /></td>
		    <td><label for="<?php echo $this->get_field_id('open_newtab'); ?>">Open links in new tab</label></td>
		    <td><label for="<?php echo $this->get_field_id('strip_title'); ?>">Strip Title</label></td>
		    <td><input id="<?php echo $this->get_field_id('strip_title');?>" name="<?php echo $this->get_field_name('strip_title'); ?>" type="text" value="<?php echo $strip_title; ?>" class="widefat" title="The number of charaters to be displayed. Use 0 to disable stripping"/></td>
	      </tr>
		  <tr>
		    <td height="29"><input id="<?php echo $this->get_field_id('show_thumb'); ?>" type="checkbox"  name="<?php echo $this->get_field_name('show_thumb'); ?>" value="1" <?php echo $show_thumb == "1" ? 'checked="checked"' : ""; ?> /></td>
		    <td><label for="<?php echo $this->get_field_id('show_thumb'); ?>">Show thumbnail if present</label></td>
		    <td>&nbsp;</td>
		    <td>&nbsp;</td>
	      </tr>
		</table>
		</div>
		
		<div class="srr_settings">
		<h4>Other settings</h4>
		<table width="100%" height="109" border="0">
		  <tr>
			<td height="32"><label>Color style: </label></td>
			<td>
			<?php
			echo '<select name="' . $this->get_field_name('color_style') . '" id="' . $this->get_field_id('color_style') . '">';
			foreach($srr_color_styles as $k => $v){
				echo '<option value="' . $v . '" ' . ($color_style == $v ? 'selected="selected"' : "") .  '>' . $k . '</option>';
			}
			echo '</select>';
			?>
			</td>
		  </tr>
		  <tr>
			<td height="33"><label for="<?php echo $this->get_field_id('enable_ticker'); ?>">Ticker animation:</label> </td>
			<td><input id="<?php echo $this->get_field_id('enable_ticker'); ?>" type="checkbox"  name="<?php echo $this->get_field_name('enable_ticker'); ?>" value="1" <?php echo $enable_ticker == "1" ? 'checked="checked"' : ""; ?> /></td>
		  </tr>
		  <tr>
			<td height="36"><label for="<?php echo $this->get_field_id('visible_items');?>">Visible items: </label></td>
			<td><input id="<?php echo $this->get_field_id('visible_items');?>" name="<?php echo $this->get_field_name('visible_items'); ?>" type="text" value="<?php echo $visible_items; ?>" class="widefat" title="The no of feed items to be visible."/>
			</td>
		  </tr>
		  <tr>
			<td height="36"><label for="<?php echo $this->get_field_id('ticker_speed');?>">Ticker speed: </label></td>
			<td><input id="<?php echo $this->get_field_id('ticker_speed');?>" name="<?php echo $this->get_field_name('ticker_speed'); ?>" type="text" value="<?php echo $ticker_speed; ?>" title="Speed of the ticker in seconds"/> seconds
			</td>
		  </tr>
		</table>
		</div>
		
		<div class="srr_support"> <a href="http://facebook.com/aakashweb" class="srr_fblike" target="_blank">Like</a> | <a href="http://bit.ly/srrDonate" target="_blank" style="color: #FF6600" title="If you like this plugin, then just make a small donation and it will be helpful for the plugin's development.">Donate</a> | <a href="http://www.aakashweb.com/wordpress-plugins/super-rss-reader/" target="_blank">Support</a></div>
		<small>Please donate and share this plugin to show your support. Thank you :)</small>
		
		<?php
	}
}

function super_rss_reader_init(){
	register_widget('super_rss_reader_widget');
}
add_action('widgets_init', 'super_rss_reader_init');

function srr_widget_scripts(){
	if(in_array($GLOBALS['pagenow'], array('widgets.php'))){
	?>
	<style type="text/css">
		.srr_settings h4{
			border-bottom: 1px solid #DFDFDF;
			margin: 20px -11px 10px -11px;
			padding: 5px 11px 5px 11px;
			border-top: 1px solid #DFDFDF;
			background-color: #fff;
			background-image: -ms-linear-gradient(top,#fff,#f9f9f9);
			background-image: -moz-linear-gradient(top,#fff,#f9f9f9);
			background-image: -o-linear-gradient(top,#fff,#f9f9f9);
			background-image: -webkit-gradient(linear,left top,left bottom,from(#fff),to(#f9f9f9));
			background-image: -webkit-linear-gradient(top,#fff,#f9f9f9);
			background-image: linear-gradient(top,#fff,#f9f9f9);
		}
		.srr_support{
			border: 1px solid #DFDFDF;
			padding: 5px 13px;
			background: #F9F9F9;
			text-decoration:none;
			margin: 10px -13px;
		}
		.srr_support a{
			text-decoration: none;
		}
		.srr_support a:hover{
			text-decoration: underline;
		}
		.srr_smalltext{
			font-size: 11px;
			color: #666666;
		}
		.srr_support{
			border: 1px solid #DFDFDF;
			padding: 5px 13px;
			background: #F9F9F9;
			text-decoration:none;
			margin: 10px -13px;
		}
		.srr_support a{
			text-decoration: none;
		}
		.srr_support a:hover{
			text-decoration: underline;
		}
		.srr_fblike{
			background: url('<?php echo SRR_URL;  ?>images/like-button.png') no-repeat;
			padding-left: 19px;
		}
		.srr_fblike span{
			display: none;
		}
		.srr_fblike:hover span{
			display: inline;
			padding:10px;
			margin: -15px 0px 0px -50px;
			position:absolute;
			background:#ffffff;
			border:1px solid #cccccc;
			-webkit-box-shadow: 0px 2px 3px rgba(0, 0, 0, 0.09);
			-moz-box-shadow: 0px 2px 3px rgba(0, 0, 0, 0.09);
			box-shadow: 0px 2px 3px rgba(0, 0, 0, 0.09);
			-moz-border-radius: 5px;
			border-radius: 5px;
		}
	</style>
	
	<script type="text/javascript">
		jQuery(document).ready(function(){
			var social = '<iframe src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Fwww.facebook.com%2Faakashweb&amp;send=false&amp;layout=button_count&amp;width=75&amp;show_faces=true&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21&amp;appId=106994469342299" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:75px; height:21px;" allowTransparency="true"></iframe>';
			
			jQuery('.srr_fblike').live('mouseenter', function(){
				if(jQuery('.srr_fblike span').length == 0)
					jQuery(this).prepend('<span>' + social + '</span>');
			});
						
		});
	</script>
	
	<?php
	}
}
add_action('admin_footer', 'srr_widget_scripts');

?>