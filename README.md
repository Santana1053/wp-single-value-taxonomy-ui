# wp-single-value-taxonomy-ui

Description
This infrastructure plugin adds basic UI for single-valued taxonomies, i.e. a taxonomy with presents a select widget.

USAGE
When registering your custom taxonomy, add the argument single_value set to true to get the single value UI. If a selection of this term is required, also add required set to true.
<pre>
register_taxonomy(
    'astrological_sign',
    array( 'person' ),
    array(
        'hierarchical' => false,
        'show_ui' => true,
        'required' => true,
        'single_value' => true
    )
);
</pre>
Development of this plugin supported by MIT Global Shakespeares.
