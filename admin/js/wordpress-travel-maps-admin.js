// Flight
// https://developers.google.com/maps/documentation/javascript/examples/geometry-headings
(function( $ ) {

	// USE STRICT
    "use strict";

    var travel_maps = {

        init : function () {

        	travel_maps.data = {};
        	travel_maps.waypoints = [];

        	travel_maps.autocompleteStart = {};
			travel_maps.autocompleteEnd = {};

        	// HTML Fields
        	travel_maps.travelmode_field = $('.wordpress-travel-map-travelmode');
        	travel_maps.calculate_route_button = $('.wordpress-travel-map-calculate-route');
        	travel_maps.add_waypoint_button = $('#wordpress-travel-map-add-waypoint');
        	travel_maps.travelmode = $(".wordpress-travel-map-travelmode option:selected").val();

        	travel_maps.post_id = $('input#post_ID').val();

        	// Directions Service
        	travel_maps.directionsService = new google.maps.DirectionsService();

        	// Directions Renderer
			travel_maps.directionsRenderer = new google.maps.DirectionsRenderer({'draggable':true});
            travel_maps.initMap();
            
            travel_maps.addWaypoint();
            travel_maps.waypointRemove();
            travel_maps.calculateButton();
            travel_maps.travelModeChange();
            travel_maps.maybeMapDragged();
            travel_maps.autocomplete();

            travel_maps.getRoute();
        },
        initMap : function () {

			travel_maps.map = new google.maps.Map(document.getElementById("wordpress-travel-map"), { zoom: 2, center: {lat: 34, lng: -40.605} });
			travel_maps.directionsRenderer.setMap(travel_maps.map);

        },
        maybeWaypointsChanged : function () {

        	travel_maps.waypoints = [];

			var waitpointInputs = $('.wordpress-travel-map-waypoint');
			waitpointInputs.each(function(i, index) {

				var address = $(index).val();

				if (address !== "") {
					travel_maps.waypoints.push({
						location: address,
						stopover: true
					});
				}
			});

        },
		maybeMapDragged : function() {

			travel_maps.directionsRenderer.addListener('directions_changed', function(){
				travel_maps.saveRoute();
			});

        },
		calculateAndDisplayRoute : function () {
			travel_maps.directionsService.route({
				origin: $('.wordpress-travel-map-start').val(),
				destination: $('.wordpress-travel-map-end').val(),
				waypoints: travel_maps.waypoints,
				optimizeWaypoints: true,
				travelMode: travel_maps.travelmode
			}, function(response, status) {
				if (status === google.maps.DirectionsStatus.OK) {
					travel_maps.directions = response;
					travel_maps.directionsRenderer.setDirections(travel_maps.directions);
					travel_maps.saveRoute();
				} else {
					window.alert('Directions request failed due to ' + status);
				}
			});
		},
		saveRoute : function () {

			travel_maps.buildDirections();

			travel_maps.data.travelmode = $(".wordpress-travel-map-travelmode option:selected").val();

			$.ajax({
			    method: "POST",
			    url: ajaxurl, 
			    data: {
			        'action': 'save_directions',
			        'id':   travel_maps.post_id,
			        'directions' : JSON.stringify(travel_maps.data)
			    }, 
			    function(response){
			        alert('The server responded: ' + response);
			    }
			});
		},
		buildDirections : function () {

			var rleg_count = travel_maps.directionsRenderer.directions.routes[0].legs.length;

            travel_maps.data.start = {
                'lat':travel_maps.directionsRenderer.directions.routes[0].legs[0].start_location.lat(), 
                'lng':travel_maps.directionsRenderer.directions.routes[0].legs[0].start_location.lng()
            };
            travel_maps.data.end = {
                'lat':travel_maps.directionsRenderer.directions.routes[0].legs[rleg_count-1].end_location.lat(), 
                'lng':travel_maps.directionsRenderer.directions.routes[0].legs[rleg_count-1].end_location.lng()
            };

            var w = [];
            var route = travel_maps.directionsRenderer.directions.routes[0];

            for (var l = 0; l < route.legs.length; l++) 
            {
	            if(l != 0) {
	                w.push({
	                    location:{
	                    	'lat':route.legs[l].start_location.lat(), 
	                    	'lng':route.legs[l].start_location.lng()
	                    },
	                    stopover:true
	                });
	            }
	            for(var j = 0; j < route.legs[l].via_waypoints.length; j++) {
	                w.push({
	                    location: {
	                    	'lat':route.legs[l].via_waypoints[j].lat(), 
	                    	'lng':route.legs[l].via_waypoints[j].lng()
	                    },
	                    stopover:false
	                });
                }
            }

            travel_maps.data.waypoints = w;
		},
		getRoute : function () {

			$.ajax({
				method: "POST",
			    url: ajaxurl, 
			    dataType: 'json',
			    data: {
			        'action': 'get_directions',
			        'id':   travel_maps.post_id
			    },
			}).done(function(directions) {

				var wp = [];
				if(directions == null) {
					return false;
				}

				if(directions.waypoints.length > 0) {
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

		        travel_maps.directionsService.route(request, function(res,sts){
		            if(sts=='OK')
		                travel_maps.directionsRenderer.setDirections(res);
		        }); 

			}).fail(function(jqXHR, textStatus, errorThrown) {
				console.log(jqXHR);
				console.log(textStatus);
				console.log(errorThrown);
			});
		},
        addWaypoint : function () {

			travel_maps.add_waypoint_button.on('click', function(e) {
				e.preventDefault();
				var lastWaypoint = $('.wordpress-travel-map-waypoint-container:last').clone().appendTo( ".wordpress-travel-map-waypoints" );
				new google.maps.places.Autocomplete($(lastWaypoint).find('input')[0], {});
			});
        },
        calculateButton : function () {

			travel_maps.calculate_route_button.on('click', function(e) {
				e.preventDefault();
				travel_maps.travelmode_field.trigger('change');

			});
        },
        travelModeChange : function () {

			travel_maps.travelmode_field.on('change', function(e) {
				e.preventDefault();

				travel_maps.travelmode = $(this).val();
				travel_maps.data.travelmode = $(this).val();

				travel_maps.maybeWaypointsChanged();
				travel_maps.calculateAndDisplayRoute();
			});
        },
        autocomplete : function () {

			travel_maps.autocompleteStart = new google.maps.places.Autocomplete($(".wordpress-travel-map-start")[0], {});
			travel_maps.autocompleteStart.addListener('place_changed', function () {
				travel_maps.startChanged(this);
			});

			travel_maps.autocompleteEnd = new google.maps.places.Autocomplete($(".wordpress-travel-map-end")[0], {});

        	$(".wordpress-travel-map-waypoint").each(function(i, index) {
        		new google.maps.places.Autocomplete($(index)[0], {});
        	});
        },
        startChanged : function (a) {

			var place = a.getPlace();
			
			if (place.geometry) {
				travel_maps.map.panTo(place.geometry.location);
				travel_maps.map.setZoom(12);
			}
        },
        waypointRemove : function () {
        	$( ".wordpress-travel-map-waypoints" ).on( "click", ".wordpress-travel-map-waypoint-remove", function(e) {
        		e.preventDefault();

        		$(this).closest('.wordpress-travel-map-waypoint-container').remove();
        	});
        }
    };

    $(document).ready(function() {
    	if( $('body.post-type-travel_maps').length > 0) {
    		travel_maps.init();
    	}
	});

})( jQuery );
