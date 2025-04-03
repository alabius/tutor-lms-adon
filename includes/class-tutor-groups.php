<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Tutor_Groups {

    public function __construct() {
        // Register the Group custom post type
        add_action('init', [$this, 'register_group_cpt']);

        // Add the co-facilitator meta box
        add_action('add_meta_boxes', [$this, 'add_co_faci_meta_box']);
        
        // Save co-facilitator information when group is saved
        add_action('save_post_group', [$this, 'save_co_faci_meta'], 10, 3);
    }

    // Register Group Custom Post Type
    public function register_group_cpt() {
        $labels = array(
            'name'                  => 'Groups',
            'singular_name'         => 'Group',
            'menu_name'             => 'Groups',
            'name_admin_bar'        => 'Group',
            'add_new'               => 'Add New',
            'add_new_item'          => 'Add New Group',
            'new_item'              => 'New Group',
            'edit_item'             => 'Edit Group',
            'view_item'             => 'View Group',
            'all_items'             => 'All Groups',
            'search_items'          => 'Search Groups',
            'parent_item_colon'     => 'Parent Groups:',
            'not_found'             => 'No groups found.',
            'not_found_in_trash'    => 'No groups found in Trash.',
            'featured_image'        => 'Group Image',
            'set_featured_image'    => 'Set group image',
            'remove_featured_image' => 'Remove group image',
            'use_featured_image'    => 'Use as group image',
            'archives'              => 'Group archives',
            'insert_into_item'      => 'Insert into group',
            'uploaded_to_this_item' => 'Uploaded to this group',
            'filter_items_list'     => 'Filter groups list',
            'items_list_navigation' => 'Groups list navigation',
            'items_list'            => 'Groups list',
        );

        $args = array(
            'labels'               => $labels,
            'public'               => true,
            'publicly_queryable'   => true,
            'show_ui'              => true,
            'show_in_menu'         => true,
            'query_var'            => true,
            'rewrite'              => array('slug' => 'group'),
            'capability_type'      => 'post',
            'hierarchical'         => false,
            'menu_icon'            => 'dashicons-groups',
            'supports'             => array('title', 'editor', 'thumbnail'),
            'show_in_rest'         => true,
        );

        register_post_type('group', $args);
    }

    // Add a meta box for co-facilitator selection
    public function add_co_faci_meta_box() {
        add_meta_box(
            'co_faci_meta_box',
            'Co-Facilitator',
            [$this, 'render_co_faci_meta_box'],
            'group',
            'side',
            'default'
        );
    }

    // Render the meta box for selecting co-facilitator
    public function render_co_faci_meta_box($post) {
        // Get current co-facilitator if exists
        $co_faci_id = get_post_meta($post->ID, '_co_faci_id', true);

        // Display select dropdown of users
        $users = get_users(['role__in' => ['administrator', 'editor']]);
        echo '<select name="co_faci_id" id="co_faci_id" class="postbox">';
        echo '<option value="">Select Co-Facilitator</option>';
        foreach ($users as $user) {
            echo '<option value="' . esc_attr($user->ID) . '"' . selected($co_faci_id, $user->ID, false) . '>' . esc_html($user->display_name) . '</option>';
        }
        echo '</select>';
    }

    // Save co-facilitator meta data
    public function save_co_faci_meta($post_id, $post, $update) {
        if ('group' != $post->post_type) {
            return;
        }

        // Save or update the co-facilitator ID
        if (isset($_POST['co_faci_id'])) {
            update_post_meta($post_id, '_co_faci_id', sanitize_text_field($_POST['co_faci_id']));
        }
    }
}

// Initialize the Groups functionality
new Tutor_Groups();
