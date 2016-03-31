<?php
add_action( 'init', array( 'Tenzing_Nav_Menu_Item_Custom_Fields', 'setup' ) );
class Tenzing_Nav_Menu_Item_Custom_Fields {
    static $options = array(
        'item_tpl' => '
			<p class="additional-menu-field-{name} description description-wide">
				<label for="edit-menu-item-{name}-{id}">
					{label}<br>
					<textarea name="menu-item-{name}[{id}]" id="edit-menu-item-{name}-{id}" cols="30" rows="3" class="widefat code edit-menu-item-{name}">{value}</textarea>
				</label>
				<span class="description">The tooltip will be displayed in the top level menu.</span>
			</p>
		',
        'item_tpl_original' => '
			<p class="additional-menu-field-{name} description description-thin">
				<label for="edit-menu-item-{name}-{id}">
					{label}<br>
					<input
						type="{input_type}"
						id="edit-menu-item-{name}-{id}"
						class="widefat code edit-menu-item-{name}"
						name="menu-item-{name}[{id}]"
						value="{value}">
				</label>
				<span class="description">The tooltip will be displayed in the top level menu.</span>
			</p>
		'
    );
    static function setup() {
        if ( !is_admin() )
            return;
        $new_fields = apply_filters( 'tenzing_nav_menu_item_additional_fields', array() );
        if ( empty($new_fields) )
            return;
        self::$options['fields'] = self::get_fields_schema( $new_fields );
        add_filter( 'wp_edit_nav_menu_walker', function () {
            return 'Tenzing_Walker_Nav_Menu_Edit';
        });
        add_action( 'save_post', array( __CLASS__, '_save_post' ), 10, 2 );
    }
    static function get_fields_schema( $new_fields ) {
        $schema = array();
        foreach( $new_fields as $name => $field) {
            if (empty($field['name'])) {
                $field['name'] = $name;
            }
            $schema[] = $field;
        }
        return $schema;
    }
    static function get_menu_item_postmeta_key($name) {
        return '_menu_item_' . $name;
    }
    /**
     * Inject the
     * @hook {action} save_post
     */
    static function get_field( $item, $depth, $args ) {
        $new_fields = '';
        foreach( self::$options['fields'] as $field ) {
            $field['value'] = get_post_meta($item->ID, self::get_menu_item_postmeta_key($field['name']), true);
            $field['id'] = $item->ID;
            $new_fields .= str_replace(
                array_map(function($key){ return '{' . $key . '}'; }, array_keys($field)),
                array_values(array_map('esc_attr', $field)),
                self::$options['item_tpl']
            );
        }
        return $new_fields;
    }
    /**
     * Save the newly submitted fields
     * @hook {action} save_post
     */
    static function _save_post($post_id, $post) {
        if ( $post->post_type !== 'nav_menu_item' ) {
            return $post_id; // prevent weird things from happening
        }
        foreach( self::$options['fields'] as $field_schema ) {
            $form_field_name = 'menu-item-' . $field_schema['name'];
            if (isset($_POST[$form_field_name][$post_id])) {
                $key = self::get_menu_item_postmeta_key($field_schema['name']);
                $value = stripslashes($_POST[$form_field_name][$post_id]);
                update_post_meta($post_id, $key, $value);
            }
        }
    }
}
// requiring the nav-menu.php file on every page load is not so wise
require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
class Tenzing_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {
    function start_el(&$output, $item, $depth, $args) {
        $item_output = '';
        parent::start_el($item_output, $item, $depth, $args);
        if ( $new_fields = Tenzing_Nav_Menu_Item_Custom_Fields::get_field( $item, $depth, $args ) ) {
            $item_output = preg_replace('/(?=<div[^>]+class="[^"]*submitbox)/', $new_fields, $item_output);
        }
        $output .= $item_output;
    }
}
// Somewhere in config...
add_filter( 'tenzing_nav_menu_item_additional_fields', 'mytheme_menu_item_additional_fields' );
function mytheme_menu_item_additional_fields( $fields ) {
    $fields['tooltip'] = array(
        'name' => 'tooltip',
        'label' => __('Tooltip content', 'xteam'),
        'container_class' => 'link-tooltip',
        'input_type' => 'text',
    );

    return $fields;
}