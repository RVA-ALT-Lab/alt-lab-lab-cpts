<?php 
/*
Plugin Name: ALT Lab Lab CPTS
Plugin URI:  https://github.com/
Description: For creating a new set of custom post types for labs and other orgs.
Version:     1.0
Author:      ALT Lab
Author URI:  http://altlab.vcu.edu
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: my-toolset

*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


add_action('wp_enqueue_scripts', 'alt_lab_lab_load_scripts');

function alt_lab_lab_load_scripts() {                           
    $deps = array('jquery');
    $version= '1.0'; 
    $in_footer = true;    
    wp_enqueue_script('alt-lab-lab-main-js', plugin_dir_url( __FILE__) . 'js/alt-lab-lab-main.js', $deps, $version, $in_footer); 
    wp_enqueue_style( 'alt-lab-lab-main-css', plugin_dir_url( __FILE__) . 'css/alt-lab-lab-main.css');
}


/*
*
*
*ACF FRONT
*
*/
function alt_lab_lab_faculty_content($content) {
global $post;
    if ($post->post_type === 'faculty' && in_the_loop() && is_main_query()) {
      $content =  alt_lab_lab_faculty_data($post) . $content;
    }
      return $content;
}


add_filter('the_content', 'alt_lab_lab_faculty_content');



//for single faculty pages
function alt_lab_lab_faculty_data($post){
  $post_id = $post->ID;
  $html = '<div class="lab-faculty-data" id="lab-faculty-holder">';
  $title = get_field('title', $post_id);
  $expertise = get_field('area_of_expertise', $post_id);
  $location = get_field('location', $post_id);
  $phone = get_field('phone', $post_id);
  $email = get_field('email', $post_id);
  $website_url = get_field('website_url', $post_id);
  $website_title = get_field('website_title', $post_id);
  $html .= '<div class="lab-row">';
  if ($title){
    $html .= '<div class="lab-label lab-title-label">Title:</div><div class="lab-content lab-title-content">' . $title . '</div></div>';
  } 
  if ($expertise){
    $html .= '<div class="lab-row"><div class="lab-label lab-expertise-label">Expertise:</div><div class="lab-content lab-expertise-content">' . $expertise . '</div></div>';
  } 
   if ($location){
    $html .= '<div class="lab-row"><div class="lab-label lab-location-label">Location:</div><div class="lab-content lab-location-content">' . $location . '</div></div>';
  } 
   if ($phone){
    $html .= '<div class="lab-row"><div class="lab-label lab-phone-label">Phone:</div><div class="lab-content lab-phone-content">' . $phone . '</div></div>';
  } 
  if ($html){
    $html .= '<div class="lab-row"><div class="lab-label lab-email-label">Email:</div><div class="lab-content lab-email-content"><a href="mailto:'.$email.'">' . $email . '</a></div></div>';
  }
  if ($website_url){
    $html .= '<div class="lab-row"><div class="lab-label lab-website-label">Website:</div><div class="lab-content lab-website-content"><a href="' . $website_url . '">' . $website_title . '</a></div>';
  }
  return $html . '</div></div>';
}


//shortcode for faculty content by type
function lab_all_faculty( $atts, $content = null ) {
    extract(shortcode_atts( array(
         'type' => '',  
    ), $atts));     

    $html ='';
    $type = htmlspecialchars_decode($type);
    $args = array(
          'posts_per_page' => -1,
          'post_type'   => 'faculty', 
          'post_status' => 'publish', 
          'orderby' => 'name', 
          'order' => 'ASC',                
          'meta_query' => array(
          'relation'    => 'OR',
          // array(
          //   'key'   => 'group',
          //   'value'   => $type,
          //   'compare' => 'LIKE'
          // ),
      )
      //do the published option and consider sorting
    );
          // query
      $the_query = new WP_Query( $args );
          if( $the_query->have_posts() ): 
            $html .= '<div class="faculty-holder">';
            while ( $the_query->have_posts() ) : $the_query->the_post(); 
            
              $post_id = get_the_ID();
              $title = get_field('title', $post_id);
              $expertise = get_field('area_of_expertise', $post_id);
              $location = get_field('location', $post_id);
              $phone = get_field('phone', $post_id);
              $email = get_field('email', $post_id);
              $website_url = get_field('website_url', $post_id);
              $website_title = get_field('website_title', $post_id);

            $html .= '<div class="the-faculty">';
              if ( has_post_thumbnail() ) {
              $html .=  get_the_post_thumbnail(get_the_ID(),'faculty-img', array('class' => 'faculty-bio-image responsive', 'alt' => 'The faculty biography picture for ' . get_the_title() .'.'));
              }  
             $html .= '<h2 class="faculty-title">';
             $html .=  '<a href="' . get_permalink() . '">' .get_the_title() . '</a>';
             $html .= '</h2>';
              if ($title){
                $html .= '<div class="lab-title">' . $title . '</div>';
              } 
              //  if ($email){
              //   $html .= '<div class="lab-email">' . $email . '</div>';
              // } 
             $html .= '</div>';          
           endwhile;
           $html .= '</div>';
        endif;
  wp_reset_query();  // Restore global post data stomped by the_post().
   return $html;
}

add_shortcode( 'all-faculty', 'lab_all_faculty' );


//add new media size
add_image_size( 'faculty-img', 220, 220, array( 'center', 'center' ) ); // Hard crop center center


/*
*
*
*ACF FOUNDATION
*
*/


 
 //ACF JSON SAVER
  add_filter('acf/settings/save_json', 'alt_lab_lab_json_save_point');
   
  function alt_lab_lab_json_save_point( $path ) {
      
      // update path
      $path = plugin_dir_path(__FILE__) . '/acf-json';
      
      // return
      return $path;
      
  }

  //ACF JSON LOADER
  add_filter('acf/settings/load_json', 'alt_lab_lab_acf_json_load_point');

  function alt_lab_lab_acf_json_load_point( $paths ) {
      
      // remove original path (optional)
      unset($paths[0]);    
      
      // append path
      $path = plugin_dir_path(__FILE__) . '/acf-json';
      
      // return
      return $paths;
      
  }


/*
*
*
*CUSTOM POST TYPES
*
*/

// Register Custom Post Type faculty
// Post Type Key: faculty
function create_faculty_cpt() {

  $labels = array(
    'name' => __( 'Faculty', 'Post Type General Name', 'textdomain' ),
    'singular_name' => __( 'Faculty', 'Post Type Singular Name', 'textdomain' ),
    'menu_name' => __( 'Faculty', 'textdomain' ),
    'name_admin_bar' => __( 'Faculty', 'textdomain' ),
    'archives' => __( 'Faculty Archives', 'textdomain' ),
    'attributes' => __( 'Faculty Attributes', 'textdomain' ),
    'parent_item_colon' => __( 'Parent faculty:', 'textdomain' ),
    'all_items' => __( 'All faculty', 'textdomain' ),
    'add_new_item' => __( 'Add New Faculty', 'textdomain' ),
    'add_new' => __( 'Add New', 'textdomain' ),
    'new_item' => __( 'New Faculty', 'textdomain' ),
    'edit_item' => __( 'Edit Faculty', 'textdomain' ),
    'update_item' => __( 'Update Faculty', 'textdomain' ),
    'view_item' => __( 'View Faculty', 'textdomain' ),
    'view_items' => __( 'View Faculty', 'textdomain' ),
    'search_items' => __( 'Search Faculty', 'textdomain' ),
    'not_found' => __( 'Not found', 'textdomain' ),
    'not_found_in_trash' => __( 'Not found in Trash', 'textdomain' ),
    'featured_image' => __( 'Featured Image', 'textdomain' ),
    'set_featured_image' => __( 'Set featured image', 'textdomain' ),
    'remove_featured_image' => __( 'Remove featured image', 'textdomain' ),
    'use_featured_image' => __( 'Use as featured image', 'textdomain' ),
    'insert_into_item' => __( 'Insert into faculty', 'textdomain' ),
    'uploaded_to_this_item' => __( 'Uploaded to this faculty', 'textdomain' ),
    'items_list' => __( 'faculty list', 'textdomain' ),
    'items_list_navigation' => __( 'faculty list navigation', 'textdomain' ),
    'filter_items_list' => __( 'Filter faculty list', 'textdomain' ),
  );
  $args = array(
    'label' => __( 'faculty', 'textdomain' ),
    'description' => __( 'the great people we work with', 'textdomain' ),
    'labels' => $labels,
    'menu_icon' => '',
    'supports' => array('title', 'editor', 'revisions', 'author', 'trackbacks', 'custom-fields', 'thumbnail',),
    'taxonomies' => array(),
    'public' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'menu_position' => 5,
    'show_in_admin_bar' => true,
    'show_in_nav_menus' => true,
    'can_export' => true,
    'has_archive' => true,
    'hierarchical' => false,
    'exclude_from_search' => false,
    'show_in_rest' => true,
    'publicly_queryable' => true,
    'capability_type' => 'post',
    'menu_icon' => 'dashicons-universal-access-alt',
  );
  register_post_type( 'faculty', $args );
  
  global $wp_rewrite;
  $wp_rewrite->flush_rules();
}
add_action( 'init', 'create_faculty_cpt', 0 );

//publication custom post type

// Register Custom Post Type publication
// Post Type Key: publication
function create_publication_cpt() {

  $labels = array(
    'name' => __( 'Publication', 'Post Type General Name', 'textdomain' ),
    'singular_name' => __( 'Publication', 'Post Type Singular Name', 'textdomain' ),
    'menu_name' => __( 'Publication', 'textdomain' ),
    'name_admin_bar' => __( 'Publication', 'textdomain' ),
    'archives' => __( 'Publication Archives', 'textdomain' ),
    'attributes' => __( 'Publication Attributes', 'textdomain' ),
    'parent_item_colon' => __( 'Parent Publication:', 'textdomain' ),
    'all_items' => __( 'All Publications', 'textdomain' ),
    'add_new_item' => __( 'Add New Publication', 'textdomain' ),
    'add_new' => __( 'Add New', 'textdomain' ),
    'new_item' => __( 'New Publication', 'textdomain' ),
    'edit_item' => __( 'Edit Publication', 'textdomain' ),
    'update_item' => __( 'Update Publication', 'textdomain' ),
    'view_item' => __( 'View Publication', 'textdomain' ),
    'view_items' => __( 'View Publications', 'textdomain' ),
    'search_items' => __( 'Search Publications', 'textdomain' ),
    'not_found' => __( 'Not found', 'textdomain' ),
    'not_found_in_trash' => __( 'Not found in Trash', 'textdomain' ),
    'featured_image' => __( 'Featured Image', 'textdomain' ),
    'set_featured_image' => __( 'Set featured image', 'textdomain' ),
    'remove_featured_image' => __( 'Remove featured image', 'textdomain' ),
    'use_featured_image' => __( 'Use as featured image', 'textdomain' ),
    'insert_into_item' => __( 'Insert into Publication', 'textdomain' ),
    'uploaded_to_this_item' => __( 'Uploaded to this Publication', 'textdomain' ),
    'items_list' => __( 'Publication list', 'textdomain' ),
    'items_list_navigation' => __( 'Publication list navigation', 'textdomain' ),
    'filter_items_list' => __( 'Filter Publication list', 'textdomain' ),
  );
  $args = array(
    'label' => __( 'Publication', 'textdomain' ),
    'description' => __( 'the great work done by our faculty', 'textdomain' ),
    'labels' => $labels,
    'menu_icon' => '',
    'supports' => array('title', 'editor', 'revisions', 'author', 'trackbacks', 'custom-fields', 'thumbnail',),
    'taxonomies' => array(),
    'public' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'menu_position' => 5,
    'show_in_admin_bar' => true,
    'show_in_nav_menus' => true,
    'can_export' => true,
    'has_archive' => true,
    'hierarchical' => false,
    'exclude_from_search' => false,
    'show_in_rest' => true,
    'publicly_queryable' => true,
    'capability_type' => 'post',
    'menu_icon' => 'dashicons-format-aside',
  );
  register_post_type( 'publication', $args );
  
  global $wp_rewrite;
  $wp_rewrite->flush_rules();
}
add_action( 'init', 'create_publication_cpt', 0 );



//LOGGER -- like frogger but more useful

if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}

  //print("<pre>".print_r($a,true)."</pre>");
