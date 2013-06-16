<?php
/*
* Plugin Name: Chapter Map
* Plugin URI: https://bitbucket.org/wikitopian/chapter-map
* Description: Designate BuddyPress groups as chapters, with a map
* Version: 0.1
* Author: Matt Parrott
* Author URI: http://www.swarmstrategies.com/matt
* License: GPLv2
* */

class Chapter_Map {

    public function __construct() {

        add_action( 'bp_groups_admin_meta_boxes', array( &$this, 'add_map_box' ) );

        add_action( 'admin_init', array( &$this, 'map_box_save' ) );
    }

    public function add_map_box() {

        add_meta_box(
            'bp_group_chapter_map',
            'Chapter Map',
            array( &$this, 'map_box' ),
            get_current_screen()->id,
            'side',
            'core'
        );

    }

    public function map_box( $item ) {

        error_log( 'map_box' );

        $group_id = $item->id;

        $is_chapter = groups_get_groupmeta( $item->id, 'chapter_map_is_chapter' );
        $latitude   = groups_get_groupmeta( $item->id, 'chapter_map_latitude' );
        $longitude  = groups_get_groupmeta( $item->id, 'chapter_map_longitude' );

        wp_nonce_field( plugin_basename( __FILE__ ), 'chapter_map_nonce' );

        echo "<input type='hidden' name='chapter_map_group_id' value ='{$group_id}' />";

        echo "<input type='checkbox' name='chapter_map_is_chapter' value='1' ";
        checked( $is_chapter );
        echo "  /> ";

        echo "Chapter<br /><br />";

        echo "<input type='text' name='chapter_map_latitude' value='{$latitude}' size='10' /> ";

        echo "Latitude<br />";

        echo "<input type='text' name='chapter_map_longitude' value='{$longitude}' size='10' /> ";

        echo "Longitude<br />";



    }

    public function map_box_save() {

        if(
            !isset( $_POST['chapter_map_nonce'] )
            ||
            !wp_verify_nonce( $_POST['chapter_map_nonce'], plugin_basename( __FILE__ ) ) 
        ) {
            return;
        }

        $group_id = intval( $_POST['chapter_map_group_id'] );

        $is_chapter = $_POST['chapter_map_is_chapter'];
        $latitude  = filter_var( $_POST['chapter_map_latitude'],  FILTER_VALIDATE_FLOAT );
        $longitude = filter_var( $_POST['chapter_map_longitude'], FILTER_VALIDATE_FLOAT );

        groups_update_groupmeta( $group_id, 'chapter_map_is_chapter', $is_chapter );
        groups_update_groupmeta( $group_id, 'chapter_map_latitude', $latitude );
        groups_update_groupmeta( $group_id, 'chapter_map_longitude', $longitude );

    }

}

$chapter_map = new Chapter_Map();

?>
