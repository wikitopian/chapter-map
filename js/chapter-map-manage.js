jQuery( document ).ready( function( $ ) {
    chapter_map_manage( $ );
});

var chapter_map_manage = function( $ ) {

    $( 'a.add-new-h2' ).click( function(){
        form_add_new();
    });

    $( '#chapter_map_manage_add_edit_save' ).click( function() {
        form_save( fetch_id() );
    });

    $( '#chapter_map_manage_add_edit_cancel' ).click( function() {
        form_clear();
    });

    $( 'a.chapter-map-manage-edit' ).click( function() {

        $( '#chapter_map_manage_add_edit' ).show();

        var id = fetch_id( this );

        $.ajax({
            url: ajaxurl,
            data: {
                action: 'chapter_map_get_chapter',
            id: id
            },
            success: function( response ) {

                response = $.parseJSON( response );
                response = response[0];

                for( item in response ) {

                    var field = "input[id='chapter_map_manage["+item+"]']";
                    $( field ).val( response[item] );

                }

            }

        });

    });

    $( 'a.chapter-map-manage-delete' ).click( function() {

        var id = fetch_id( this );

        $.ajax({
            url: ajaxurl,
            data: {
                action: 'chapter_map_delete_item',
            id: id
            },
            success: function( response ) {
                form_clear( response );
            }

        });

    });

    var form_add_new = function() {

        $( '#chapter_map_manage_add_edit' ).show();

    }

    var form_save = function( id ){

        var elems = $( "input[id^='chapter_map_manage[']" );

        var chapter = new Object();
        var chapter_regex = /chapter_map_manage\[(\w+)/;
        $.each( elems, function( key, elem ) {

            var chapter_name =  $( elem ).attr( 'name' );
            chapter_name = chapter_regex.exec( chapter_name );
            chapter_name = chapter_name[1];

            var chapter_value = $( "input[id='chapter_map_manage[" + chapter_name + "]']" ).val();
            chapter[chapter_name] = chapter_value;

        });

        $.ajax({
            url: ajaxurl,
            data: {
                action: 'chapter_map_add_edit_item',
            id: id,
            chapter: chapter
            },
            success: function( response ) {

                form_clear( response );

            }

        });

    }

    var form_clear = function( response ) {

        $( "input[id^='chapter_map_manage[']" ).val( '' );

        $( '#chapter_map_manage_add_edit' ).hide();

        if( response ) {

            $( 'div.wrap' ).empty();

            response = $.parseJSON( response );

            table = response['table'];

            $( 'div.wrap' ).html( table );

            chapter_map_manage( $ );

        }

    }

    var fetch_id = function( obj ) {

        if( obj ) {

            var id = $( obj ).attr( 'id' );

            var id_regex = /chapter-map-manage-\w+-(\d+)/;
            var id = id_regex.exec( id );
            var id = id[1];

            return id;

        } else {

            return $( "input[id='chapter_map_manage[id]']" ).val();

        }

    }

}

