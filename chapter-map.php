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

        echo "Test";

    }

}

$chapter_map = new Chapter_Map();

?>
