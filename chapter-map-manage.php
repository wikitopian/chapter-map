<?php

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

add_action(
    'wp_ajax_chapter_map_get_chapter',
    array( 'Chapter_Map_Manage', 'do_get_chapter' )
);

add_action(
    'wp_ajax_chapter_map_add_edit_item',
    array( 'Chapter_Map_Manage', 'do_add_edit_item' )
);

add_action(
    'wp_ajax_chapter_map_delete_item',
    array( 'Chapter_Map_Manage', 'do_delete_item' )
);

class Chapter_Map_Manage extends WP_List_Table {
    public $found_data = array();

    private $chapters = array();

    public function __construct() {

        parent::__construct( array(
            'singular' => 'chapter',
            'plural'   => 'chapters',
            'ajax'     => true
        ));

    }

    public static function do_get_chapter() {
        error_log( print_r( $_GET, true ) );

        $chapter = Chapter_Map_Data::get_chapter( $_GET['id'] );

        $chapter = json_encode( $chapter);

        echo $chapter;

        wp_die();
    }

    public static function do_add_edit_item() {

        $success = Chapter_Map_Data::do_save_item( $_GET['chapter'] );

        self::do_table_refresh();

        wp_die();
    }

    public static function do_delete_item() {

        $success = Chapter_Map_Data::do_delete_item( $_GET['id'] );

        self::do_table_refresh();

    }

    public static function do_table_refresh() {

        $GLOBALS['hook_suffix'] = 'chapter_map_manage';
        $new_table = new Chapter_Map_Manage();

        $new_table->prepare_items();

        ob_start();

        $new_table->display();

        $display = ob_get_clean();

        $return = array(
            'status' => 'success',
            'table'  => $display
        );

        $return = json_encode( $return );

        echo $return;

        wp_die();
    }

    public function display() {

        echo '<div class="wrap"><h2>Manage Chapter Map';
        echo '<a href="#" class="add-new-h2">Add New</a>';
        echo '</h2>';

        $dir = plugin_dir_path( __FILE__ );
        include "{$dir}/snippets/chapter-map-manage-table-add-edit.php";

        parent::display();

        echo '</div>';

    }

    public function no_items() {
        echo 'No chapters found';
    }

    public function column_default( $item, $column_name ) {
        return $item[$column_name];
    }

    public function get_columns() {

        $columns = array(
            'cb' => '<input type="checkbox"/>',
            'label' => 'Chapter',
            'contact' => 'Contact',
            'email' => 'Email',
            'url' => 'Hyperlink',
            'lat' => 'Latitude',
            'lng' => 'Longitude',
        );

        return $columns;
    }

    public function get_sortable_columns() {

        $sortable_columns = array(
            'label' => array( 'label', false ),
            'contact' => array( 'contact', false ),
            'email' => array( 'email', false ),
            'url' => array( 'url', false ),
            'lat' => array( 'lat', false ),
            'lng' => array( 'lng', false),
        );

        return $sortable_columns;
    }

    public function usort_reorder( $a, $b ) {

        $orderby = ( !empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'label';
        $order = ( !empty( $_GET['order'] ) ) ? $_GET['order'] : 'asc';
        $result = strcmp( $a[$orderby], $b[$orderby] );

        return ( $order === 'asc' ) ? $result : -$result;
    }

    public function column_label( $item ) {

        $hyperlink  = "<a href='#' ";
        $hyperlink .= "id='chapter-map-manage-%s-%s' ";
        $hyperlink .= "class='chapter-map-manage-%s' >%s</a>";

        $actions = array(

            'edit' => sprintf(
                $hyperlink,
                'edit',
                $item['id'],
                'edit',
                'Edit'
            ),

            'delete' => sprintf(
                $hyperlink,
                'delete',
                $item['id'],
                'delete',
                'Delete'
            ),

        );

        return sprintf(
            '%1$s %2$s',
            $item['label'],
            $this->row_actions( $actions )
        );

    }

    public function column_cb( $item ) {

        return sprintf(
            '<input type="checkbox" name="chapter[]" value="%s"/>',
            $item['id']
        );

    }

    public function prepare_items() {

        $this->chapters = Chapter_Map_Data::get_table_chapters();

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
        usort( $this->chapters, array( &$this, 'usort_reorder' ) );

        $per_page = 5;
        $current_page = $this->get_pagenum();
        $total_items = count( $this->chapters );

        $this->found_data = array_slice(
            $this->chapters,
            ( ( $current_page - 1 ) * $per_page ),
            $per_page
        );

        $this->set_pagination_args( array(
            'total_items'=>$total_items,
            'per_page'=>$per_page
        ) );

        $this->items = $this->found_data;
    }

}

/* EOF */
