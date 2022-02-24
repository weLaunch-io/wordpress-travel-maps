
(function( $ ) {

	// USE STRICT
    "use strict";

    var travel_maps = {

        init : function (elements) {

        	travel_maps.maps = {};
        	travel_maps.elements = $(elements);
        	travel_maps.adminurl = travel_maps_options.ajax_url;

        	travel_maps.elements.each(function(i, index) {

        		travel_maps.maps[i] = {};

	        	travel_maps.maps[i].id = $(index).data('id');

	        	// Directions Service
	        	travel_maps.maps[i].directionsService = new google.maps.DirectionsService();

	        	// Directions Renderer
				travel_maps.maps[i].directionsRenderer = new google.maps.DirectionsRenderer();
	            travel_maps.maps[i].map = new google.maps.Map($(index).get(0), { zoom: 2, center: {lat: 34, lng: -40.605} });
	            travel_maps.maps[i].directionsRenderer.setMap(travel_maps.maps[i].map);

	            travel_maps.getRoute(travel_maps.maps[i]);

        	});
        },
		getRoute : function (map) {

			$.ajax({
				method: "POST",
			    url: travel_maps.adminurl,
			    dataType: 'json',
			    data: {
			        'action': 'get_directions',
			        'id':   map.id,
			    },
			}).done(function(directions) {

				var wp = [];

				if(directions.waypoints !== "undefined" && directions.waypoints.length > 0) {
			        for(var i=0; i < directions.waypoints.length; i++) {
			            wp[i] = {
			                'location': new google.maps.LatLng(directions.waypoints[i].location.lat, directions.waypoints[i].location.lng),
			                'stopover': directions.waypoints[i].stopover
			            }
			        }
			    }

		        var request = {
		            'origin':new google.maps.LatLng(directions.start.lat, directions.start.lng),
		            'destination':new google.maps.LatLng(directions.end.lat, directions.end.lng),
		            'waypoints': wp,
		            optimizeWaypoints: false,
		            avoidHighways: false,
		            avoidTolls: false,
		            travelMode: directions.travelmode
		        }

		        map.directionsService.route(request, function(res,sts){
		            if(sts=='OK')
		                map.directionsRenderer.setDirections(res);
		        }); 

			}).fail(function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR);
				console.log(textStatus);
				console.log(errorThrown);
			});
		},
    };

    $(document).ready(function() {
    	if( $('.wordpress-travel-map').length > 0) {
			travel_maps.init( $('.wordpress-travel-map') );
    	}
	});

})( jQuery );
