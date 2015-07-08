var map;
function initialize() {
    var mapOptions = {
        zoom: parseFloat(chapter_map.zoom),
        center: new google.maps.LatLng(
                chapter_map.latitude,
                chapter_map.longitude
        ),
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    if( !jQuery( '#map-canvas' ).length ) {
        return;
    }

    map = new google.maps.Map(document.getElementById('map-canvas'),
            mapOptions);

    var chapters = chapter_map.chapters;
    for(chapter_id in chapters) {
        var chapter = chapters[chapter_id];

        chapter.coord = new google.maps.LatLng(chapter.lat,chapter.lng);

        chapter.marker = new google.maps.Marker({
              position: chapter.coord,
              map: map,
              title: chapter.name + ' Chapter'
        });

        chapter.marker.infowindow  = new google.maps.InfoWindow();

        chapter.marker.infowindow.setContent(

            ''
            + '<a href="'
            + chapter.url
            + '">'
            + chapter.avatar
            + '</a><br />'
            + '<a href="'
            + chapter.url
            + '"><p style="font-weight: bold; text-align: center;">'
            + chapter.label
            + ' Chapter'
            + '</p></a>'

        );

        google.maps.event.addListener(chapter.marker, 'click', function () {

            this.infowindow.open(map, this);

        });

        var list_item = document.createElement( "li" );
        list_item.innerHTML = '<a href="'
            + chapter.url
            + '">'
            + chapter.label
            + ' Chapter'
            + '</a>';

        var list = document.getElementById('chapter-map-list');
        list.appendChild(list_item);

    }

}

google.maps.event.addDomListener(window, 'load', initialize);
