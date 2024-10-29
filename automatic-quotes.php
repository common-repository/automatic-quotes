<?php
/*
Plugin Name: Automatic Quotes
Plugin URI: http://www.quotepiper.com/plugin/wordpress-widget/
Description: Get new interesting quotes delivered daily to your website.
Version: 2.1
Author: Quote Piper
Author URI: http://www.quotepiper.com/
License: GPLv2 or later


    

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
 * Register the new widget.
 */
add_action('widgets_init', 'qp_widgets_register');

function qp_widgets_register() {
	register_widget('QP_Quotes');
}

/**
 * Add the stylesheet to the site.
 */
if(!is_admin() )
	wp_enqueue_style(
		'quotepiper',
		WP_PLUGIN_URL . '/automatic-quotes/quotepiper.css',
		array(),
		'1.0',
		'screen'
	);


/**
 * Create the widget.
 */
class QP_Quotes extends WP_Widget {
	
	//
	// Construct the widget
	//
	function QP_Quotes() {
		// Widget settings
		$widget_ops = array(
			'classname' => 'qp-quotes',
			'description' => 'Embed new interesting quotes daily.'
		);

		// Create the widget.
		$this->WP_Widget('qp-quotes', 'Automatic Quotes', $widget_ops);
	}

	//
	// Widget ouput
	//
	function widget($args, $instance) {
		extract($args);

		// Get the settings
		$formatting = $instance['formatting'];
		
		if($formatting)
			echo $before_widget;
		
			// Create the widget box.
			qp_quotes_view();
		
		if($formatting)
			echo $after_widget;
	}

	//
	// Update the settings
	//
	function update($new_instance, $old_instance) {
		$instance = $old_instance;

		/* Get the new value. */
		$instance['formatting'] = strip_tags($new_instance['formatting']);

		return $instance;
	}

	//
	// Settings form for the widget
	//
	function form($instance) {

		/* Set up some default widget settings. */
		$defaults = array('formatting' => false );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		
		<!-- Display widget formatting? : Checkbox -->
		<p>
			<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'formatting' ); ?>" name="<?php echo $this->get_field_name( 'formatting' ); ?>" value="display" <?php if($instance['formatting']) { echo 'checked="checked" '; } ?>/> 
			<label for="<?php echo $this->get_field_id( 'formatting' ); ?>"> Display widget formatting?</label>
		</p>

	<?php
	}
}

/**
 * Create the HTML for the widget and output it.
 */
function qp_quotes_view() { ?>	
	<div id="quotepiper">
		<p><?php echo qp_get_quotes(); ?></p>
		
		<a href="javascript: void(0)" title="" id="qp-logo2" target="none"></a>
		
	</div>
<?php }


/**
 * Get a quote. If our cached quote is out of date, fetch a new one.
 * @return String. The quote.
 */
function qp_get_quotes() {

	// Configuration






	$fileURL = 'http://www.quotepiper.com/plugin-quote.txt';
	$transName = 'quote-piper-quotes3'; // Name of value in database.
	$cacheTime = 60 * 24; // Time in minutes between updates.
	
	// Do we already have a saved commenter?
	$wisdom = get_transient($transName);
	 
	// If not, lets get one.
	if($wisdom === false) :
	
		// Use wp_remote_get to get the text file with the quote.
		$file = wp_remote_get($fileURL);
		
		// Get the text from the file.
		if(!is_wp_error($response) ) {
			// We got the file.
			$wisdom = $file['body'];
			
			// Sanitize, just to be sure.
			$wisdom = esc_attr($wisdom);
			
			// Save our new transient.
			set_transient($transName, $wisdom, 60 * $cacheTime);
			
		} else {
			// It failed, so use a sample quote and try again in 10 minutes!
			// Let's give them a quote about patience. :D
			
			$wisdom = '"Patience is bitter, but its fruit is sweet." -Aristotle';
			
			// Save our new transient for a shorter time.
			set_transient($transName, $wisdom, 60 * 10);
		}
		 
	endif;
	
	// Now send back the result.
	return $wisdom;
}


?>
