<?php
/*
Author: Stephen Harris http://profiles.wordpress.org/stephenh1988/
Github: https://github.com/stephenh1988

Hacker: Jim Greenleaf http://jim.greenle.af
Github: https://github.com/aMoniker

This is a class implementation of the wp.tuts+ tutorial: http://wp.tutsplus.com/tutorials/creative-coding/how-to-use-radio-buttons-with-taxonomies/

It's been updated to allow for multiple uses.

To use it, include it in your functions.php (or somewhere in your plugin)
If you add it to your theme, also add the javascript file to your theme’s js folder (call it radiotax.js)
If you add it to your plugin, pass 'script_src' as an additional argument, which should point to the radiotax.js file.

Once you've included it, simply call:

new WordPress_Radio_Taxonomy('taxonomy_name', array(
     'post_type'              => 'your_post_type'           // defaults to 'post'
    ,'taxonomy_metabox_id'    => 'custom_metabox_id'        // usually not necessary to set
    ,'taxonomy_metabox_title' => 'Your Metabox Title'       // defaults to ucwords('taxonomy_name')
    ,'script_src'             => '/path/to/the/radiotax.js' // defaults to your theme's js directory
));

*/

class WordPress_Radio_Taxonomy {
    public $taxonomy = '';
    public $taxonomy_metabox_id = '';
    public $taxonomy_metabox_title = '';
    public $post_type = '';
    public $script_src = '';

    public function __construct($taxonomy, $args = array()) {
        $this->taxonomy = $taxonomy;

        $this->taxonomy_metabox_id = isset($args['taxonomy_metabox_id'])
                                   ? $args['taxonomy_metabox_id']
                                   : $taxonomy . 'div'
                                   ;

        $this->taxonomy_metabox_title = isset($args['taxonomy_metabox_title'])
                                      ? $args['taxonomy_metabox_title']
                                      : ucwords($taxonomy)
                                      ;

        $this->post_type = isset($args['post_type'])
                         ? $args['post_type']
                         : $post_type
                         ;

        $this->script_src = isset($args['script_src'])
                          ? $args['script_src']
                          : get_template_directory_uri() . '/js/radiotax.js'
                          ;

        //Remove old taxonomy meta box  
        add_action('admin_menu', array($this, 'remove_meta_box'));

        //Add new taxonomy meta box  
        add_action('add_meta_boxes', array($this, 'add_meta_box'));  

        //Load admin scripts
        add_action('admin_enqueue_scripts',array($this, 'admin_script'));

        //Load admin scripts
        add_action('wp_ajax_radio_tax_add_taxterm',array($this, 'ajax_add_term'));
    }

    public function remove_meta_box() { 
        remove_meta_box($this->taxonomy_metabox_id, $this->post_type, 'normal');  
    }

    public function add_meta_box() {
        add_meta_box($this->taxonomy . '_id', $this->taxonomy_metabox_title, array($this, 'metabox'), $this->post_type, 'side', 'core');  
    }

    // Callback to set up the metabox
    public function metabox($post) {
        // Get taxonomy and terms
        $taxonomy = $this->taxonomy;

        // Set up the taxonomy object and get terms
        $tax = get_taxonomy($taxonomy);
        $terms = get_terms($taxonomy, array('hide_empty' => 0));
      
        // Name of the form
        $name = 'tax_input[' . $taxonomy . ']';
      
        // Get current and popular terms
        $popular = get_terms($taxonomy, array('orderby' => 'count', 'order' => 'DESC', 'number' => 10, 'hierarchical' => false));  
        $postterms = get_the_terms($post->ID, $taxonomy);
        $current = $postterms ? array_pop($postterms) : false;
        $current = $current ? $current->term_id : 0; ?>
      
        <div id="taxonomy-<?php echo $taxonomy; ?>" class="categorydiv">
            <ul id="<?php echo $taxonomy; ?>-tabs" class="category-tabs">
                <li class="tabs"><a href="#<?php echo $taxonomy; ?>-all" tabindex="3"><?php echo $tax->labels->all_items; ?></a></li>
                <li class="hide-if-no-js"><a href="#<?php echo $taxonomy; ?>-pop" tabindex="3"><?php _e('Most Used'); ?></a></li>
            </ul>

            <div id="<?php echo $taxonomy; ?>-all" class="tabs-panel">
                <ul id="<?php echo $taxonomy; ?>checklist" class="list:<?php echo $taxonomy?> categorychecklist form-no-clear"><?php
                foreach($terms as $term):
                    $id = $taxonomy . '-' . $term->term_id;
                    $value = ' value="' .(is_taxonomy_hierarchical($taxonomy) ? $term->term_id : $term->term_slug). '"'; ?>
                    <li id="<?php echo $id; ?>"><label class="selectit">
                        <input type="radio" id="in-<?php echo $id; ?>" name="<?php echo $name; ?>" <?php echo checked($current, $term->term_id, false); echo $value; ?>>&nbsp;<?php echo $term->name; ?><br>
                    </label></li><?php
                 endforeach; ?>
                </ul>
            </div>

            <div id="<?php echo $taxonomy; ?>-pop" class="tabs-panel" style="display: none;">
                <ul id="<?php echo $taxonomy; ?>checklist-pop" class="categorychecklist form-no-clear"><?php
                foreach($popular as $term):
                    $id = 'popular-' . $taxonomy . '-' . $term->term_id;
                    $value = ' value="' .(is_taxonomy_hierarchical($taxonomy) ? $term->term_id : $term->term_slug). '"'; ?>
                    <li id="<?php echo $id; ?>"><label class="selectit">
                        <input type="radio" id="in-<?php echo $id; ?>" <?php echo checked($current, $term->term_id, false); echo $value; ?>><?php echo $term->name; ?><br>
                    </label></li><?php
                endforeach; ?>
                </ul>
            </div>

            <p id="<?php echo $taxonomy; ?>-add" class="">
                <label class="screen-reader-text" for="new<?php echo $taxonomy; ?>"><?php echo $tax->labels->add_new_item; ?></label>
                <input type="text" name="new<?php echo $taxonomy; ?>" id="new<?php echo $taxonomy; ?>" class="form-required form-input-tip" value="<?php echo esc_attr( $tax->labels->new_item_name ); ?>" tabindex="3" aria-required="true">
                <input type="button" id="" class="radio-tax-add button" value="<?php echo esc_attr($tax->labels->add_new_item); ?>" tabindex="3">
                <?php wp_nonce_field( 'radio-tax-add-'.$taxonomy, '_wpnonce_radio-add-tag', false ); ?>
            </p>
        </div><?php
    }

    public function admin_script() {
        wp_register_script('radiotax', $this->script_src, array('jquery'), null, true);
        wp_localize_script('radiotax', 'radio_tax', array('slug' => $this->taxonomy));
        wp_enqueue_script('radiotax');
    }

    public function ajax_add_term() {
        $taxonomy = !empty($_POST['taxonomy']) ? $_POST['taxonomy'] : '';
        $term = !empty($_POST['term']) ? $_POST['term'] : '';
        $tax = get_taxonomy($taxonomy);

        check_ajax_referer('radio-tax-add-'.$taxonomy, '_wpnonce_radio-add-tag');

        if (!$tax || empty($term)) { exit(); }
        if (!current_user_can($tax->cap->edit_terms)) { die('-1'); }

        $tag = wp_insert_term($term, $taxonomy);

        if (!$tag || is_wp_error($tag) || (!$tag = get_term($tag['term_id'], $taxonomy ))) {
            exit(); // TODO: Error handling
        }
    
        $id = $taxonomy . '-' . $tag->term_id;
        $name = "tax_input[$taxonomy]";
        $value = 'value="' .(is_taxonomy_hierarchical($taxonomy) ? $tag->term_id : $term->tag_slug). '"';
        $html ='<li id="'.$id.'"><label class="selectit"><input type="radio" id="in-'.$id.'" name="'.$name.'" '.$value.'>'. $tag->name.'</label></li>';
    
        echo json_encode(array('term' => $tag->term_id, 'html' => $html));
        exit();
    }
}

?>