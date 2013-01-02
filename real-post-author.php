<?php 

/*
Plugin Name: Real Post Author Meta Box
Plugin URI: 
Description: Ability to credit the real author of a post or page without having the author be a member of this blog.
Author: CTLT Dev
Version: 1.0
Author URI: 

*/

Class Real_Post_Author_Meta_Box {
	static $instance;
	
	function __construct() {
		self::$instance = $this;
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * init function.
	 * 
	 * @access public
	 * @return void
	 */
	function init(){
		// filters
		add_filter( "get_the_author_display_name", array( $this, "the_author" ) , 10 , 3 );
		add_filter( "the_author", array( $this, "the_author") , 10 , 3 ); /* just in case */
		
		add_filter( "author_link", array( $this, "the_author_link") , 10 , 3 );
		
		
		// admin side
		
		/* Use the admin_menu action to define the custom boxes */
		add_action('admin_menu', array( $this, 'init_meta_box' ) );
	
		/* Use the save_post action to do something with the data entered */
		add_action('save_post',  array( $this, 'save_meta_data' ) );

	}

	/**
	 * the_author function.
	 * 
	 * @access public
	 * @param mixed $author
	 * @return void
	 */
	function the_author( $author ) {
 		global $post;
 		$custom_field = get_post_meta( $post->ID, '_real_post_author' , true );
 		if( !empty($custom_field) )
 			return $custom_field;
 		
 		return $author; # return default
	}
	
	/**
	 * the_author_link function.
	 * 
	 * @access public
	 * @param mixed $link
	 * @return void
	 */
	function the_author_link($link) {
 		global $post;
 		$custom_field = get_post_meta( $post->ID, '_real_post_author' , true );
 		if( !empty($custom_field) )
 			return get_permalink($post->ID);
 	
 		return $link;
	}
	
	// ADMIN SIDE
	/* Adds a custom section to the "advanced" Post and Page edit screens */
	
	/**
	 * init_meta_box function.
	 * 
	 * @access public
	 * @return void
	 */
	function init_meta_box() {
		// on posts 
		add_meta_box( 'clf_base_custom_fields_real_post_author_box', __( 'Author', 'clf_base' ), array( $this, 'display_meta_box' ), 'post', 'advanced','low' );
		remove_meta_box( 'authordiv' , 'post' , 'normal' ); 
		
		// on pages
		add_meta_box( 'clf_base_custom_fields_real_post_author_box', __( 'Author', 'clf_base' ), array( $this, 'display_meta_box' ), 'page', 'advanced','low' );
		remove_meta_box( 'authordiv' , 'page' , 'normal' ); 
		
		// todo: does it apply to other post types? 
		
	}
	
	
	/**
	 * meta_box_display function.
	 * 
	 * @access public
	 * @return void
	 */
	function display_meta_box() {
		global $post;
		$real_post_author = get_post_meta( $post->ID, '_real_post_author', true );
   		
   		// Use nonce for verification
   		echo '<input type="hidden" name="real_post_author_noncename" id="real_post_author_noncename" value="' .wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	
	   	// The actual fields for data entry
	   	if( function_exists('post_author_meta_box') ):
   			post_author_meta_box( $post ); ?>
		  	or 
		  	<?php endif ;?>
		  	<input type="text" name="real_post_author" size="20" value="<?php echo esc_attr( $real_post_author ); ?>" /> (type in the name you want to see displayed) 
		  	<?php
	}
	
	
	/**
	 * save_meta_data function.
	 * 
	 * @access public
	 * @param mixed $post_id
	 * @return void
	 */
	function save_meta_data( $post_id ) {
		
	
		// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
		// to do anything
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return $post_id;
		
		if ( !wp_verify_nonce( $_POST['real_post_author_noncename'], plugin_basename(__FILE__) ))
				return $post_id;
		
		// only update the data if it is a string	
		if( is_string( $_POST['real_post_author'] ) )
			add_post_meta( $post_id, '_real_post_author', $_POST['real_post_author'], true) or update_post_meta( $post_id, '_real_post_author', $_POST['real_post_author'] );
		
		return $post_id;

	}

}

new Real_Post_Author_Meta_Box;
