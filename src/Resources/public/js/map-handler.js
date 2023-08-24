var markers = [],
    geocoder;

$('select[name=land]').on('change', function(){
    $('input[name=plz]').val('');
    $('input[name=ort]').val('');
});

var defaultPin = new google.maps.MarkerImage('../files/main_thema/resources/images/assets/map-markar-active-patner.png',
    new google.maps.Size(62, 56),
    new google.maps.Point(0,0),
    new google.maps.Point(30, 48));
    
var premiumPin = new google.maps.MarkerImage('../files/main_thema/resources/images/assets/markar-new.png',
    new google.maps.Size(62, 56),
    new google.maps.Point(0,0),
    new google.maps.Point(30, 48));
    
var activePin = new google.maps.MarkerImage('../files/main_thema/resources/images/assets/map-markar-partner.png',
    new google.maps.Size(82, 74),
    new google.maps.Point(0,0),
    new google.maps.Point(40, 63));

var premiumactivePin = new google.maps.MarkerImage('../files/main_thema/resources/images/assets/map-markar-active-new.png',
    new google.maps.Size(82, 74),
    new google.maps.Point(0,0),
    new google.maps.Point(40, 63));

var googlemap = {

    map: false,

    initialize: function () {

        var mapOptions = {
          zoom: zoom,
          center: new google.maps.LatLng(lat, lng)
        };

        googlemap.map = new google.maps.Map(document.getElementById('map'), mapOptions);

        if(typeof borderdata == "object"){
            var bounds = new google.maps.LatLngBounds();

            $.each(borderdata, function( index, value ) {
                var myLatLng = new google.maps.LatLng(value.latitude, value.longitude);
                bounds.extend(myLatLng);
            });

            googlemap.map.fitBounds(bounds);
        }

        googlemap.drawMarkers();

        if (!borderdata && navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(pos){
            var lat = pos.coords.latitude,
            lng = pos.coords.longitude;

            var coords = new google.maps.LatLng(lat, lng);

            googlemap.map.setCenter(coords);

            googlemap.getNearestDealers(5000, coords);

            });
         }
    },

    getNearestDealers: function(rad, coords){

        var radius = new google.maps.Circle({
            center : coords,
            radius : rad,
            fillOpacity : 0,
            strokeOpacity : 0,
            map : googlemap.map
        });


        var bounds = radius.getBounds();

        var l = 0,
            markersInside = [];

        for(var i=0; i<markers.length; i++){
            if(bounds.contains(markers[i].getPosition())){
                markersInside.push(markers[i]);
                l++;
            }

            if(l > 2){
                break;
            }
        }

        if(markersInside.length > 2){
            googlemap.map.fitBounds(bounds);
        }else{
            googlemap.getNearestDealers(rad + 10000, coords);
        }

    },

    drawMarkers: function (){

        $.each(JSON.parse(mapdata), function( index, value ) {
            markers.push(googlemap.createMarker(value));
        });

        var clusterStyles = [
            {
                textColor: 'white',
                url: '../files/main_thema/resources/images/assets/map-marker-cluster.png',
                height: 90,
                width: 90
            },
            {
                textColor: 'white',
                url: '../files/main_thema/resources/images/assets/map-marker-cluster.png',
                height: 90,
                width: 90
            },
            {
                textColor: 'white',
                url: '../files/main_thema/resources/images/assets/map-marker-cluster.png',
                height: 90,
                width: 90
            }];

        var markerCluster = new MarkerClusterer(googlemap.map, markers, clusterStyles);
        markerCluster.setStyles(clusterStyles);

    },

    createMarker: function (markerdata){
        var myLatLng = new google.maps.LatLng(markerdata.latitude, markerdata.longitude);
        var marker_type = markerdata.member_type;
        
        var marker = new google.maps.Marker({
                id: markerdata.id,
                position: myLatLng,
                map: googlemap.map,
                type: markerdata.member_type,
                title: markerdata.name,
                icon: (marker_type == "GFH") ? premiumPin : defaultPin
                
            }),
            hanedlerdetailItems = $('.hanedlerdetail'),
            scrollBar = $('.store-location-bar');

        google.maps.event.addListener(marker, 'click', function() {
        	$.ajax({
				type: 'POST',
				url: location.href,
				data: {'memberId':this.id,'REQUEST_TOKEN':$('#REQUEST_TOKEN').val(),'mode':'memberDetails'},	
				cache: false,
				success: function (memberDetails) {
					$('#partner-details').html(memberDetails);
				}
			});
        	//console.log('ID:: '+this.id);
        	
        	//console.log(this.type);
            var activeElement = $(['#hd_', this.id].join(''));
            hanedlerdetailItems.removeClass('is-active');
            activeElement.addClass('is-active');
            setActiveMarker(marker);

            if(!detailonly){
                scrollBar.scrollTop(activeElement[0].offsetTop - 90);
            }
        });

        return marker;

    }

};

$( window ).load(function() {
    if (!window.matchMedia('(max-width: 767px)').matches) {
        googlemap.initialize();
    }
});

(function () {
    if(!detailonly){
        var hanedlerdetailItems = $('.hanedlerdetail'),
            scrollBar = $('.store-location-bar');

        hanedlerdetailItems.click(function (evt) {
            var activeElement = $(this),
                id = activeElement.attr('id').replace(/[A-Za-z$-/_]/g, ""),
                marker = $.grep(markers, function(e){ return e.id == id; })[0];
            
            if(activeElement.hasClass('is-active')) {
                hanedlerdetailItems.removeClass('is-active');
                removeActiveMarker();
            } else {
                hanedlerdetailItems.removeClass('is-active');
                activeElement.addClass('is-active');
                setActiveMarker(marker);
                googlemap.map.setCenter(marker.getPosition());
            }
        });
    } else {

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (e) {
                var lat = e.coords.latitude;
                var lng = e.coords.longitude;
                geocoder = new google.maps.Geocoder();
                codeLatLng(lat, lng);
            });
        }    
    }
}());


function codeLatLng(lat, lng) {
    var latlng = new google.maps.LatLng(lat, lng);
    geocoder.geocode({'latLng': latlng}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            if (results[1]) {
                var location = {};

                for (var i=0; i<results[0].address_components.length; i++) {
                    for (var b=0;b<results[0].address_components[i].types.length;b++) {

                        if (results[0].address_components[i].types[b] == "administrative_area_level_1") {
                            location.city = results[0].address_components[i].long_name;
                        }

                        if (results[0].address_components[i].types[b] == "postal_code") {
                            location.zip = results[0].address_components[i].long_name;
                        }

                        if (results[0].address_components[i].types[b] == "country") {
                            location.country = results[0].address_components[i].short_name;
                        }

                    }
                }

                setInputValues(location);

            } else {
                console.log("No results found");
            }
        } else {
            console.log("Geocoder failed due to: " + status);
        }
    });
}

function setInputValues(location) {
    $('select[name=land]').val(location.country);
    $('input[name=plz]').val(location.zip);
    $('input[name=ort]').val(location.city);
}

function setActiveMarker(marker) {
	var pertner_type = marker.type;
	console.log(pertner_type);
    for (var i = 0, curMarker; curMarker = markers[i]; i++) {
        if (curMarker === marker) {
        	if(pertner_type == "GFH"){
        		curMarker.setIcon(premiumactivePin);
        	}
        	else{
        		curMarker.setIcon(activePin);
        	}
            
        } else {
            if(pertner_type == "GFH"){
        		curMarker.setIcon(premiumPin);
        	}
        	else{
        		curMarker.setIcon(defaultPin);
        	}
        }
    }
}

function removeActiveMarker(marker) {
	var pertner_icon = marker.type;
    for (var i = 0, curMarker; curMarker = markers[i]; i++) {
        if(pertner_icon == "GFH"){
        		curMarker.setIcon(premiumPin);
        	}
    	else{
    		curMarker.setIcon(defaultPin);
    	}
    }
}

var map = $('#map'),
    initialized = false;

$('.js-show-map').click(function () {
    map.toggleClass('show');

    if (!initialized) {
        googlemap.initialize();
        initialized = true;
    }

});
