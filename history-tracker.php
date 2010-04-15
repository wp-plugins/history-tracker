<?php
/*
Plugin Name: History Tracker
Plugin URI: http://olt.ubc.ca
Description: Trackes the history the users visit and displays it to them, like a browsing history
Author: oltdev
Version: 1.0
Author URI: http://olt.ubc.ca
*/

/**
 * All the actions and filters 
 *
 */
add_action('widgets_init','history_tracker_load_widgets'); // register the widget 
add_action('template_redirect','history_tracker_store_page'); // set the cookie 

// add shortcode
add_shortcode('history-tracker','history_tracker_shortcode'); // you guessed it its a shotcode


/**
 * Register our widget.
 *
 */
function history_tracker_load_widgets() {
	register_widget( 'HistoryTrackerWidget' );
}




/**
 * HistoryTrackerWidget class.
 * 
 * @extends WP_Widget
 */
class HistoryTrackerWidget extends WP_Widget {
    /** constructor */
    function HistoryTrackerWidget() {
        parent::WP_Widget(false, $name = 'History Tracker Widget');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );

        $title = apply_filters('widget_title', $instance['title']);
        echo $before_widget; 
        
        if ( $title )
	        echo $before_title . $title . $after_title;
	    if($instance['description'])
	        echo "<div class='history-tracker-description'>".$instance['description']."</div>";
	        
	    if(!empty($_COOKIE["History-Tracker"])):
	        echo "<ol class='history-tracker'>";
	        $history_tracker = explode("::,::",$_COOKIE["History-Tracker"]);
	        
	        $size = 5; // default number of history links that you want to display
	        if(is_numeric($instance['size']))
	        	$size = $instance['size']; // this can be overwritten in the options
	       
	        if($instance['order'] == "oldest"):
	        	$history_tracker = array_slice($history_tracker, 0, $size);
	        	$history_tracker = array_reverse($history_tracker);
	    	
	    	endif;
	        foreach($history_tracker as $historical_item):
	        	if($size == $i)
	        		break;
	        	
	        	$historical_item_array = explode("::::",$historical_item);
	        	echo "<li><a href='{$historical_item_array[1]}' title='go to {$historical_item_array[0]}'>{$historical_item_array[0]}</a></li>";		
	        	$i++;
	        endforeach;
	        echo "</ol>"; 
		endif;                
	    echo $after_widget; 
        
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
    	$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] 		 = strip_tags( $new_instance['title'] );
		$instance['description'] = strip_tags( $new_instance['description'] );
		$instance['size'] 		 = strip_tags( $new_instance['size'] );
		$instance['order'] 		 = strip_tags( $new_instance['order'] );

		return $instance;  
    }

    /** @see WP_Widget::form */
    function form($instance) {				
       /* Set up some default widget settings. */
		$defaults = array( 
			'title' => __('Your Browsing History', 'History_Tracker'), 
			'description' => __('', 'History_Tracker'), 
			'order' => 'newest',
			'size'	=> 5,
			);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'History_Tracker'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>

		<!-- Description:  -->
		<p>
			<textarea id="<?php echo $this->get_field_id( 'description' ); ?>"	rows="5" name="<?php echo $this->get_field_name( 'description' ); ?>"  class="widefat"><?php echo $instance['description']; ?></textarea>
		</p>
		<!-- History Size -->
		<p>
			<label for="<?php echo $this->get_field_id( 'size' ); ?>"><?php _e('History Size:', 'History_Tracker'); ?></label>
			
			<select class="widefat" id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>" >
				<?php 
				$j = 3;
				while(16> $j): ?>
				<option <?php selected( $instance['size'], $j); ?> value="<?php echo $j; ?>"><?php echo $j; ?></option>
				<?php 
					$j++;
					$j++;
				endwhile;
				?>
			</select>
			Number of links in the history. 
		</p>
		<!-- Order Selection Box: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e('Order:', 'History_Tracker'); ?></label> 
			<select class="widefat" id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>" >
				<option <?php selected( $instance['order'], 'newest' ); ?> value="newest">Newest First</option>
				<option <?php selected( $instance['order'], 'oldest' ); ?> value="oldest">Oldest First</option>
			</select>
		</p>
		
		
		<?php
    }

} // class HistoryTrackerWidget



/**
 * history_tracker_store_page function.
 * 
 * sets the cookie value 
 * @access public
 * @return void
 */
function history_tracker_store_page()
{
	global $post,$wp_query;
	$title = "Unknown"; 
	if(is_archive()):
	
	/* figure out what kind of page it is and set the title
	*/
		/* taxonomy, category and tags archives. */
		if(is_tax() || is_category() || is_tag()):
			$term = $wp_query->get_queried_object();
			$title = "Archive: ".$term->name;
		endif;
		/* Author archives. */
		if(is_author())
			$title = "Author Archive: ".get_the_author_meta( 'display_name', get_query_var( 'author' ) );
 		
 		/* Minutely and hourly archives. */
 		if ( get_query_var( 'minute' ) && get_query_var( 'hour' ) ) :
			$title = "Archive: ".  get_the_time('g:i a') ;

		/* Minutely archives. */
		elseif ( get_query_var( 'minute' ) ) :
			$title = "Archive: ".  sprintf( 'Minute %1$s', get_the_time( 'i'  ) );

		/* Hourly archives. */
		elseif ( get_query_var( 'hour' ) ) :
			$title = "Archive: ". get_the_time( 'g a' );

		/* Daily archives. */
		elseif ( is_day() ) :
			$title = "Archive: ". get_the_time( 'Y/m/j' ) ;

		/* Weekly archives. */
		elseif ( get_query_var( 'w' ) ) :
			$title = "Archive: ". sprintf( 'Week %1$s' , get_the_time( 'W') );

		/* Monthly archives. */
		elseif ( is_month() ) :
			$title = "Archive: ". get_the_time( 'Y F' ) ;

		/* Yearly archives. */
		elseif ( is_year() ) :
			$title = "Archive: ". get_the_time( 'Y' ) ;
		endif;
	endif;
	/* 404 Page. */
	if(is_404())
		$title = "Page Not Found";
	/* Search Page. */
	if(is_search())
		$title = "Search: ". esc_attr( get_search_query() );
	
	/* Home Page. */
	if(is_home())
		$title = "Home";
	
	/* Single, attachment and just a regular old page. */
	if(is_page() || is_single() || is_attachment())
		$title = $post->post_title;
	
	
	
	if(empty($_COOKIE["History-Tracker"])):
		$value = $title."::::".HTcurPageURL(); // set the first cookie if no cookie is set 
	else:
		
		$cookie_value_array = explode("::,::",$_COOKIE["History-Tracker"]);
		$new_cookie = array();
		$new_cookie[0] = $title."::::".HTcurPageURL(); 
		$i = 0; 
		foreach($cookie_value_array as $cookie_item):
			if(!(($new_cookie[0] == $cookie_item) && ($i ==0)))// is some one refreshes the page many times we don't want the history to grow
				$new_cookie[] = stripslashes($cookie_item);
			
			$i++; // increment the count 
			if($i == 15)
				break;
		endforeach;
		$value = join("::,::",$new_cookie);
		
	endif;
	$site_url = get_bloginfo("url");
	
	setcookie("History-Tracker", $value, time()+9800, "/", HTgetHost(get_bloginfo('url')) );	

}

function history_tracker_shortcode($atts)
{
	extract( shortcode_atts( array('size' => 5,'order' =>'newest'), $atts ) );

	ob_start();
	if(!empty($_COOKIE["History-Tracker"])):
        echo "<ol class='history-tracker'>";
        $history_tracker = explode("::,::",$_COOKIE["History-Tracker"]);
        
      
        if(is_numeric($instance['size']))
        	$size = $instance['size']; // this can be overwritten in the options
       
        if($order == "oldest"):
        	$history_tracker = array_slice($history_tracker, 0, $size);
        	$history_tracker = array_reverse($history_tracker);
    	
    	endif;
        foreach($history_tracker as $historical_item):
        	if($size == $i)
        		break;
        	$historical_item_array = explode("::::",$historical_item);
        	echo "<li><a href='{$historical_item_array[1]}' title='go to {$historical_item_array[0]}'>{$historical_item_array[0]}</a></li>";		
        	$i++;
        endforeach;
        echo "</ol>"; 
	endif;   
	
	return ob_get_clean();
}

/*****************
 * Helper Functions below 
 *
 */


/**
 * HTcurPageURL function.
 * 
 * return the current page url
 * 
 * @access public
 * @return string
 */
if(!function_exists("HTcurPageURL")):
	function HTcurPageURL() 
	{
 	$pageURL = 'http';
 	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 		$pageURL .= "://";
 	if ($_SERVER["SERVER_PORT"] != "80"):
 		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 	else: 
  		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 	endif;
 		
 	return $pageURL;
	}

endif; 

if(!function_exists("HTgetHost")):
/**
 * HTgetHost function.
 * 
 * return the current host of the address provided 
 * 
 * @access public
 * @param string $Address
 * @return string
 */
function HTgetHost($Address) {
   $parseUrl = parse_url(trim($Address));
   return trim($parseUrl[host] ? $parseUrl[host] : array_shift(explode('/', $parseUrl[path], 2)));
} 

endif;
