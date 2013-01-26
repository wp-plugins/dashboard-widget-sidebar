<?php
/*
Plugin Name: Dashboard Widget Sidebar
Plugin URI: http://www.iosoftgame.com/
Description: Enable regulare widgets to be used as Dashboard Widgets in admin
Version: 1.0.1
Author: IO SoftGame
Author URI: http://www.iosoftgame.com
License: None
*/
?>
<?php
	//Global variable used to track which widget to show the content of next.
	//This is required because there can not be transfered information from the "wp_add_dashboard_widget" function to the callback otherwise.
	global $dws_current_widget_index;
	$dws_current_widget_index = 0;

	// Function that outputs the contents of the dashboard widget
	function dws_dashboard_widget_function() {
		
		//Get global variables
		global $dws_current_widget_index;
		global $wp_registered_sidebars, $wp_registered_widgets;
		
		//Get sidebars
		$sidebars = wp_get_sidebars_widgets();
		//Get widgets
		$dws_widgets = $sidebars["dws-sidebar"];
		
		//Get current widget
		$id = $dws_widgets[$dws_current_widget_index];
		
		//Get the sidebar
		$sidebar = $wp_registered_sidebars["dws-sidebar"];
		
		//Check if the required data is set
		if( isset($wp_registered_widgets[$id]) && isset($wp_registered_widgets[$id]["callback"]) && isset($wp_registered_widgets[$id]["callback"][0]) )
		{
			/* Code borrowed from widget.php in the WordPress core */
			$params = array_merge(
			                array( array_merge( $sidebar, array('widget_id' => $id, 'widget_name' => $wp_registered_widgets[$id]['name']) ) ),
			                (array) $wp_registered_widgets[$id]['params']
			        );

	        // Substitute HTML id and class attributes into before_widget
	        $classname_ = '';
	        foreach ( (array) $wp_registered_widgets[$id]['classname'] as $cn ) {
	                if ( is_string($cn) )
	                        $classname_ .= '_' . $cn;
	                elseif ( is_object($cn) )
	                        $classname_ .= '_' . get_class($cn);
	        }
	        $classname_ = ltrim($classname_, '_');
	        $params[0]['before_widget'] = sprintf($params[0]['before_widget'], $id, $classname_);

	        $params = apply_filters( 'dynamic_sidebar_params', $params );

	        $callback = $wp_registered_widgets[$id]['callback'];

	        do_action( 'dynamic_sidebar', $wp_registered_widgets[$id] );
			
			if ( is_callable($callback) ) {
				//Call the function, that outputs the widget content
	               call_user_func_array($callback, $params);
	        }
			
			/* ---------------------------------------------------- */
		}
		
		//Next widget, please!
		$dws_current_widget_index++;
		
	}

	// Function used in the action hook
	function dws_add_dashboard_widgets() {
		
		global $wp_registered_sidebars, $wp_registered_widgets;
		
		//Get sidebars
		$sidebars = wp_get_sidebars_widgets();
		
		//Get widgets from the sidebar
		$dws_widgets = $sidebars["dws-sidebar"];
		
		//Add each widget to the dashboard
		foreach($dws_widgets as $id)
		{
			//Check if the required data is set
			if( isset($wp_registered_widgets[$id]) && isset($wp_registered_widgets[$id]["callback"]) && isset($wp_registered_widgets[$id]["callback"][0]) )
			{
				//Get widgets settings
				$widget = $wp_registered_widgets[$id]["callback"][0]->get_settings();
				//
				$title = ' ';
				//
				foreach($widget as $widget_settings)
				{
					//If title is present, use it!
					if(isset($widget_settings["title"]))
						$title = $widget_settings["title"];
				}
				
				//Add the widget to dashboard
				//wp_add_dashboard_widget('custom_dashboard_widget_' . $id, $title, 'dws_dashboard_widget_function');
				add_meta_box( 'custom_dashboard_widget_' . $id, $title, 'dws_dashboard_widget_function', 'dashboard', 'normal', 'high' );
					//TODO: Somehow make it possible to set the last to settings (side + priority) from the Widgets area. Look into how Visual Widget Logic does it!
					//1. Addtional param = Side ("normal" + "side")
					//2. Addtional param = Priority ("lov" + "normal" + "high" + a number)
			}
		}
	}

	// Register the new dashboard widget with the 'wp_dashboard_setup' action
	add_action('wp_dashboard_setup', 'dws_add_dashboard_widgets' );
	
	//Register the widget sidebar
	register_sidebar(array(
		'name' => __( 'Dashboard Widget Sidebar' ),
		'id' => 'dws-sidebar',
		'description' => __( 'Widgets in this area will be shown on the dashboard in admin.' ),
		'before_title' => '<div style="display: none;">',
		'after_title' => '</div>',
		'before_widget' => '',
		'after_widget' => ''
	));
?>