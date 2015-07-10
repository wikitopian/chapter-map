<?php
/*
* Plugin Name: Chapter Map
* Plugin URI: http://www.github.com/wikitopian/chapter-map
* Description: Designate BuddyPress groups as "chapters", with a Google Map
* Version: 0.1
* Author: Matt Parrott
* Author URI: http://www.swarmstrategies.com/matt
* License: GPLv2
* */

require_once( 'chapter-map-data.php' );

require_once( 'chapter-map-manage.php' );

class Chapter_Map {

    private $settings;

    public function __construct() {

        $default = array(
            'page' => 'chapters',
            'latitude' => 21.3280681,
            'longitude' => -157.7989705,
            'zoom' => 1
        );

        $this->settings = get_option( 'chapter_map_settings', $default );

        add_action( 'bp_groups_admin_meta_boxes', array( &$this, 'add_map_box' ) );

        add_action( 'admin_init', array( &$this, 'map_box_save' ) );

        add_filter( 'the_content', array( &$this, 'map_page' ) );

        add_action( 'wp_enqueue_scripts', array( &$this, 'map_page_script' ) );

        add_action( 'admin_menu', array( &$this, 'do_menu' ) );

        add_action( 'admin_enqueue_scripts', array( &$this, 'do_chapter_map_admin_script' ) );

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

        $is_chapter = intval( $_POST['chapter_map_is_chapter'] );
        $latitude  = filter_var( $_POST['chapter_map_latitude'],  FILTER_VALIDATE_FLOAT );
        $longitude = filter_var( $_POST['chapter_map_longitude'], FILTER_VALIDATE_FLOAT );

        groups_update_groupmeta( $group_id, 'chapter_map_is_chapter', $is_chapter );
        groups_update_groupmeta( $group_id, 'chapter_map_latitude', $latitude );
        groups_update_groupmeta( $group_id, 'chapter_map_longitude', $longitude );

    }

    public function map_page( $content = '' ) {

        if( strpos( $content, '[chapter_map]' ) > 0 ) {

            $map = '<div id="map-canvas"></div>';

            $content = preg_replace( '/\[chapter_map\]/', $map, $content );
        }

        if( strpos( $content, '[chapter_map_list]' ) > 0 ) {

            $list = '<ul id="chapter-map-list"></ul>';

            $content = preg_replace( '/\[chapter_map_list\]/', $list, $content );
        }

        $content .= "\n<hr />\n";


        return $content;

    }

    public function map_page_script() {

        wp_enqueue_script(
            'chapter_map_google',
            'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false'
        );

        wp_enqueue_script(
            'chapter_map',
            plugin_dir_url( __FILE__ ) . 'js/chapter-map.js'
        );

        wp_localize_script(
            'chapter_map',
            'chapter_map',
            array(
                'latitude' => $this->settings['latitude'],
                'longitude' => $this->settings['longitude'],
                'zoom' => $this->settings['zoom'],
                'chapters' => $this->get_chapters(),
            )
        );

        wp_enqueue_style(
            'chapter_map',
            plugin_dir_url( __FILE__ ) . 'css/chapter-map.css'
        );

    }

    private function get_chapters() {

        $chapters = array();

        if( function_exists( 'bp_is_active' ) && bp_has_groups() ) {
            while( bp_groups() ) {
                bp_the_group();

                $chapter_id = bp_get_group_id();
                if( groups_get_groupmeta( $chapter_id, 'chapter_map_is_chapter' ) ) {

                    $chapter = array();

                    $chapter['name'] = bp_get_group_name();

                    $chapter['url'] = bp_get_group_permalink();

                    $chapter['latitude'] = groups_get_groupmeta( $chapter_id, 'chapter_map_latitude' );
                    $chapter['longitude'] = groups_get_groupmeta( $chapter_id, 'chapter_map_longitude' );

                    $chapter['avatar'] = bp_get_group_avatar();

                    $chapters[$chapter['name']] = $chapter;

                }

            }
        } else {

            $chapters = Chapter_Map_Data::get_table_chapters( 0, 99 );

            foreach( $chapters as &$chapter ) {

                $chapter['avatar'] = get_avatar( $chapter['email'], 192 );

            }

        }

        ksort( $chapters );

        return $chapters;

    }

    public function do_menu() {

        add_menu_page(
            'Chapters',
            'Chapters',
            'edit_posts',
            'chapter-map-edit',
            array( &$this, 'do_chapter_page' )
        );

    }

    public function do_chapter_page() {

        $chapter_page = new Chapter_Map_Manage();

        $chapter_page->prepare_items();

        $chapter_page->display();

    }

    public function do_chapter_map_admin_script( $hook ) {

        if( !preg_match( '/chapter-map-edit/', $hook ) ) {
            return;
        }

        wp_enqueue_style(
            'chapter-map-manage',
            plugin_dir_url( __FILE__ ) . 'css/chapter-map-manage.css'
        );

        wp_enqueue_script(
            'chapter-map-manage',
            plugin_dir_url( __FILE__ ) . 'js/chapter-map-manage.js',
            array( 'jquery' )
        );

        wp_localize_script(
            'chapter-map-manage',
            'chapter_map_manage',
            array(
                'true' => false,
            )
        );


    }

}

$chapter_map = new Chapter_Map();

register_activation_hook(
	__FILE__,
	array( 'Chapter_Map_Data', 'do_tables' )
);

?>
