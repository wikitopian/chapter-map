<?php

class Chapter_Map_Data {

    private $settings;

    public function __construct( $settings ) {
        $this->settings = $settings;

    }

    public static function do_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$wpdb->prefix}chapter_map_chapters (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            modified TIMESTAMP,
            status CHAR(10) DEFAULT 'ACTIVE',
            label NVARCHAR(100) NOT NULL,
            contact NVARCHAR(100) NOT NULL,
            email NVARCHAR(100) NOT NULL,
            url NVARCHAR(100) NOT NULL,
            lat FLOAT (10,6),
            lng FLOAT (10,6),
            UNIQUE KEY id (id)
    ) {$charset_collate};";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

    }

    public static function get_table_chapters( $offset = 0, $limit = 10 ) {
        global $wpdb;

        $query = <<<QUERY

SELECT
    chapters.id,
    chapters.label,
    chapters.contact,
    chapters.email,
    chapters.url,
    chapters.lat,
    chapters.lng
    FROM `{$wpdb->prefix}chapter_map_chapters` AS chapters
    WHERE chapters.status  <> 'DELETED'
    ORDER BY `id` ASC;

QUERY;

        $chapters = $wpdb->get_results( $query, ARRAY_A );

        return $chapters;
    }

    public static function get_chapter( $id ) {
        global $wpdb;

        $query = <<<QUERY

SELECT
    chapters.id,
    chapters.label,
    chapters.contact,
    chapters.email,
    chapters.url,
    chapters.lat,
    chapters.lng
    FROM `{$wpdb->prefix}chapter_map_chapters` AS chapters
    WHERE chapters.id = {$id};

QUERY;

        $chapters = $wpdb->get_results( $query, ARRAY_A );

        return $chapters;
    }

    public static function do_save_item( $chapter ) {
        global $wpdb;

        foreach( $chapter as &$value ) {
            $value = htmlentities( $value );
        }

        /* if id > 0, edit existing chapter */
        if( $chapter['id'] ) {

            $query = <<<QUERY

UPDATE {$wpdb->prefix}chapter_map_chapters
    SET `label` = '%s',
        `contact` = '%s',
        `email` = %s,
        `url` = '%s',
        `lat` = '%s',
        `lng` = '%s'
    WHERE `id` = '%s';

QUERY;

            $query = $wpdb->prepare(
                $query,
                $chapter['label'],
                $chapter['contact'],
                $chapter['email'],
                $chapter['url'],
                $chapter['lat'],
                $chapter['lng'],
                $chapter['id']
            );

            $success = $wpdb->query( $query );

        } else { /* add chapter */

            $query = <<<QUERY

INSERT INTO {$wpdb->prefix}chapter_map_chapters
    (
        `label`,
        `contact`,
        `email`,
        `url`,
        `lat`,
        `lng`
    )
    VALUES(
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s'
    );

QUERY;

            $query = $wpdb->prepare(
                $query,
                $chapter['label'],
                $chapter['contact'],
                $chapter['email'],
                $chapter['url'],
                $chapter['lat'],
                $chapter['lng'],
                $chapter['id']
            );

            $success = $wpdb->query( $query );

        }

    }

    public static function do_delete_item( $id ) {
        global $wpdb;

        $query = <<<QUERY

UPDATE {$wpdb->prefix}chapter_map_chapters
    SET `status` = 'DELETED'
    WHERE `id` = '{$id}';

QUERY;

        $success = $wpdb->query( $query );

    }

    public static function geocode( $address ) {
        $address = urlencode( $address );

        $url = "http://maps.google.com/maps/api/geocode/json?sensor=false&address={$address}";

        $resp_json = file_get_contents( $url );

        $resp = json_decode ( $resp_json, true );

        if ( $resp['status'] == 'OK' ) {
            $lat = $resp['results'][0]['geometry']['location']['lat'];
            $lng = $resp['results'][0]['geometry']['location']['lng'];
            $formatted_address = $resp['results'][0]['formatted_address'];

            if ( $lat && $lng && $formatted_address ) {
                $data_arr = array();

                array_push(
                    $data_arr,
                    $lat,
                    $lng,
                    $formatted_address
                );

                return $data_arr;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}

/* EOF */
