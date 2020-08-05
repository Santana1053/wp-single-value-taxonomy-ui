<?php
/*
Plugin Name: WP Single Value Taxonomy UI
Description: This plugin adds the basic UI for single-valued taxonomies for heirarchical taxonomies. Updated version of abandonded plugin.
Version: 1.0.5
Author URI: http://functionlabs.io
*/

class WP_Single_Value_Taxonomy_UI {

	public static function init(){
		add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_boxes' ] );
	}

	public static function add_meta_boxes() {
		foreach ( get_taxonomies( [ 'show_ui' => true ], 'object' ) as $taxonomy ) {
			if ( !isset($taxonomy->single_value) || !$taxonomy->single_value ){
				continue;
			}

			foreach ( $taxonomy->object_type as $object_type ) {
				remove_meta_box( $taxonomy->name . 'div', $object_type, 'side' );

				add_meta_box( $taxonomy->name . 'div',
					$taxonomy->labels->singular_name,
					[ __CLASS__, 'metabox' ],
					$object_type,
					'side',
					'default', [
						'taxonomy' => $taxonomy->name
					]
				);
			}
		}
	}

	public static function metabox( $post, $box ) {
		if ( !isset( $box['args'] ) || !is_array( $box['args'] ) ){
			$args = [];
		} else {
			$args = $box['args'];
		}
		extract( wp_parse_args( $args ), EXTR_SKIP );
		$tax_name = $taxonomy;
		$taxonomy = get_taxonomy( $taxonomy );
		$disabled = !current_user_can($taxonomy->cap->assign_terms) ? 'disabled="disabled"' : '';
		printf( '<select name="tax_input[%s][]" %s>', esc_attr( $tax_name ), $disabled );
		if ( !isset($taxonomy->required) || !$taxonomy->required ){
			printf( '<option value="">(%s)</option>', sprintf( __('No %s'), $taxonomy->labels->singular_name ) );
		}

		wp_terms_checklist( $post->ID, [
			'taxonomy' => $taxonomy->name,
			'walker' => new Walker_Taxonomy_Select
		] );
		echo '</select>';
	}
}
add_action('init', [ 'WP_Single_Value_Taxonomy_UI', 'init' ] );

class Walker_Taxonomy_Select extends Walker {
	public $tree_type = 'taxonomy_select';
	public $db_fields = [
		'parent' => 'parent',
		'id' => 'term_id'
	];

	function start_lvl( &$output, $depth = 0, $args = [] ) {}

	function end_lvl( &$output, $depth = 0, $arg = [] ) {}

	function start_el( &$output, $term, $depth = 0, $args = [], $current_object_id = 0 ) {
		$indent = str_repeat( "&dash;", $depth ) . ' ';

		extract( $args );

		$taxonomy_obj = get_taxonomy( $taxonomy );

		$option = sprintf( '<option id="%s-%d" value="%s"%s%s>%s%s</option>',
			esc_attr( $taxonomy ),
			esc_attr( $term->term_id ),
			$taxonomy_obj->hierarchical ? esc_attr( $term->term_id ) : esc_attr( $term->slug ),
			selected( in_array( $term->term_id, $selected_cats ), true, false ),
			disabled( empty( $args['disabled'] ), false, false ),
			$indent,
			esc_html( apply_filters('the_category', $term->name ) )
		);

		$output .= "\n" . $option;
	}

	function end_el( &$output, $category, $depth = 0, $args = [] ) {}
}
