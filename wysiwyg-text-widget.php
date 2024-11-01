<?php
/*
Plugin Name: WYSIWYG Text Widget
Plugin URI: http://www.findableblogs.com/plugins/wysiwyg-text-widget/
Description: The standard Wordpress text widget, enhanced with WYSIWYG editing. For WordPress 2.5+ only.
Author: Findable Blogs
Version: 1.2
Author URI: http://www.findableblogs.com/
*/

function widget_wys_text_load() {
	// Check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;


	if ( !function_exists('htmlspecialchars_decode') )
	{
		function htmlspecialchars_decode($text)
		{
			return strtr($text, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
		}
	}


	function widget_wys_text($args, $number = 1) {
		extract($args);
		$options = get_option('widget_wys_text');
		$title = $options[$number]['title'];
		$text = apply_filters( 'widget_wys_text', $options[$number]['text'] );
		?>
			<?php echo $before_widget; ?>
			<?php if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } ?>
			<div class="wys-text"><?php echo $text; ?></div>
			<?php echo $after_widget; ?>
		<?php
	}

	function widget_wys_text_control($number) {
		$options = $newoptions = get_option('widget_wys_text');
		if ( !is_array($options) )
			$options = $newoptions = array();
		if ( $_POST["wys-text-submit-$number"] ) {
			$newoptions[$number]['title'] = strip_tags(stripslashes($_POST["wys-text-title-$number"]));
			$newoptions[$number]['text'] = stripslashes($_POST["wys-text-text-$number"]);
			if ( !current_user_can('unfiltered_html') )
				$newoptions[$number]['text'] = stripslashes(wp_filter_post_kses($newoptions[$number]['text']));
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_wys_text', $options);
		}
		$title = attribute_escape($options[$number]['title']);
		$text = format_to_edit($options[$number]['text']);

		?>

			<input style="width: 450px;" id="wys-text-title-<?php echo $number; ?>" name="wys-text-title-<?php echo $number; ?>" type="text" value="<?php echo $title; ?>" />
			<?php the_editor(htmlspecialchars_decode($text), "wys-text-text-$number", ''); ?>
			<input type="hidden" id="wys-text-submit-<?php echo "$number"; ?>" name="wys-text-submit-<?php echo "$number"; ?>" value="1" />
		<?php
	}

	function widget_wys_text_setup() {
		$options = $newoptions = get_option('widget_wys_text');
		if ( isset($_POST['wys-text-number-submit']) ) {
			$number = (int) $_POST['wys-text-number'];
			if ( $number > 16 ) $number = 16;
			if ( $number < 1 ) $number = 1;
			$newoptions['number'] = $number;
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_wys_text', $options);
			widget_wys_text_register($options['number']);
		}
	}

	function widget_wys_text_page() {
		$options = $newoptions = get_option('widget_wys_text');
	?>
		<div class="wrap">
			<form method="POST">
				<h2><?php _e('WYSIWYG Text Widgets'); ?></h2>
				<p style="line-height: 30px;"><?php _e('How many widgets would you like?'); ?>
					<select id="wys-text-number" name="wys-text-number" value="<?php echo $options['number']; ?>">
						<?php for ( $i = 1; $i < 17; ++$i ) echo "<option value='$i' ".($options['number']==$i ? "selected='selected'" : '').">$i</option>"; ?>
					</select>
					<span class="submit"><input type="submit" name="wys-text-number-submit" id="wys-text-number-submit" value="<?php echo attribute_escape(__('Save')); ?>" /></span>
				</p>
			</form>
		</div>
		<?php
	}

	function widget_wys_text_register() {
		$options = get_option('widget_wys_text');
		$number = $options['number'];
		if ( $number < 1 ) $number = 1;
		if ( $number > 16 ) $number = 16;
		for ($i = 1; $i <= 16; $i++) {
			$name = array('WYSIWYG Text %s', null, $i);
			register_sidebar_widget($name, $i <= $number ? 'widget_wys_text' : /* unregister */ '', $i);
			register_widget_control($name, $i <= $number ? 'widget_wys_text_control' : /* unregister */ '', 450, 400, $i);
		}
		add_action('sidebar_admin_setup', 'widget_wys_text_setup');
		add_action('sidebar_admin_page', 'widget_wys_text_page');
	}
	widget_wys_text_register();
}

add_action('plugins_loaded', 'widget_wys_text_load');


function widget_wys_text_init()
{
	wp_enqueue_script('editor');
	wp_enqueue_script('editor_functions');
	wp_enqueue_script('quicktags');
}
add_action('admin_print_scripts-widgets.php', 'widget_wys_text_init');


if (version_compare($wp_version, 2.7, '>='))
{
	add_action('admin_head-widgets.php', 'wp_tiny_mce');
}

?>