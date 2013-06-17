##Description

This is a class implementation of the wp.tuts+ tutorial: http://wp.tutsplus.com/tutorials/creative-coding/how-to-use-radio-buttons-with-taxonomies/

To use it, include it in your functions.php (or somewhere in your plugin)
If you add it to your theme, also add the javascript file to your theme’s js folder (call it radiotax.js)
If you add it to your plugin, pass 'script_src' as an additional argument, which should point to the radiotax.js file.

Once you've included it, simply call:

```php
new WordPress_Radio_Taxonomy('taxonomy_name', array(
     'post_type'              => 'your_post_type'           // defaults to 'post'
    ,'taxonomy_metabox_id'    => 'custom_metabox_id'        // usually not necessary to set
    ,'taxonomy_metabox_title' => 'Your Metabox Title'       // defaults to ucwords('taxonomy_name')
    ,'script_src'             => '/path/to/the/radiotax.js' // defaults to your theme's js directory
));
```

##Authors:

Stephen Harris http://profiles.wordpress.org/stephenh1988/
Github: https://github.com/stephenh1988

Jim Greenleaf http://jim.greenle.af
Github: https://github.com/aMoniker