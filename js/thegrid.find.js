var p1 = new function () {
	this.latitude;
	this.longitude;
	this.map;
	this.overlay;
	this.dragging = false;

	this.init = function() {
		if(navigator.geolocation) {
	    	navigator.geolocation.getCurrentPosition(p1.initBrowserGeoCallback);
		}
	};
	
	this.initBrowserGeoCallback = function(pos) {
		initMap(pos.coords.latitude, pos.coords.longitude);
	}

	var initMap = function(lat, lng) {
		p1.latitude = lat;
		p1.longitude = lng;
        var mapOptions = {
			center: new google.maps.LatLng(p1.latitude, p1.longitude),
			zoom: 8,
			zoomControl: false,
			panControl: false,
			streetViewControl: false,
			mapTypeControl: false,
			mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        p1.map = new google.maps.Map(document.getElementById("p1-map"), mapOptions);
			
		google.maps.event.addListener(p1.map, 'click', function(event) {
			var result = {};
			result['name'] = 'name';
			result['lat'] = event.latLng.lat();
			result['lng'] = event.latLng.lng();
			//console.log(JSON.stringify(result));
			var meters = parseInt($("#meters").val());

			for(var i=0; i<=360; i+=10) {
			    var pointB = p1.dp(event.latLng.lat(), event.latLng.lng(), i, meters);
			    var populationOptions = {
			      strokeColor: "#FF0000",
			      strokeOpacity: 0.8,
			      strokeWeight: 2,
			      fillColor: "#FF0000",
			      fillOpacity: 0.35,
			      map: p1.map,
			      center: pointB,
			      radius: 500
			    };
			    //new google.maps.Circle(populationOptions);
			}
		    var circle = {
		      strokeColor: "#FF0000",
		      strokeOpacity: 0.8,
		      strokeWeight: 2,
		      fillColor: "#FF0000",
		      fillOpacity: 0.25,
		      map: p1.map,
		      center: event.latLng,
		      radius: meters
		    };
		    if(p1.overlay)
		    	p1.overlay.setMap(null);
    		//p1.overlay = new google.maps.Circle(circle);
		});
		
		
		google.maps.event.addListenerOnce(p1.map, 'idle', function(){
		
			google.maps.event.addListener(p1.map, 'dragstart', function() {
				p1.dragging = true;
			});
			
			google.maps.event.addListener(p1.map, 'dragend', function() {
				p1.dragging = false;
			});
			
			google.maps.event.addListener(p1.map, 'bounds_changed', function() {
			});
		});
	};

	this.dp = function(lat, lng, brng, meters) {
        var dist = meters / 6371 / 1000; //mean raidus of earth is 6371km
        brng = brng.toRad();  

        var lat1 = lat.toRad(), lon1 = lng.toRad();

        var lat2 = Math.asin(Math.sin(lat1) * Math.cos(dist) + 
                             Math.cos(lat1) * Math.sin(dist) * Math.cos(brng));

        var lon2 = lon1 + Math.atan2(Math.sin(brng) * Math.sin(dist) *
                                     Math.cos(lat1), 
                                     Math.cos(dist) - Math.sin(lat1) *
                                     Math.sin(lat2));

        if (isNaN(lat2) || isNaN(lon2)) return null;

        return new google.maps.LatLng(lat2.toDeg(), lon2.toDeg());
	}
};


Number.prototype.toRad = function() {
	return this * Math.PI / 180;
}

Number.prototype.toDeg = function() {
	return this * 180 / Math.PI;
}