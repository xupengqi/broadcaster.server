var p1 = new function () {
    this.latitude;
    this.longitude;
    this.map;
    this.geocoder;
    this.currentPost;
    this.currentPostMarker;
    this.currentPostLastRefresh;
    this.markers = [];
    this.gridLines = [];
    this.dragging = false;
    this.refreshing = false;
    this.cookie_token = 'thegrid_token';
    this.cookie_userId = 'thegrid_userId';

    this.init = function(lat, lng) {
        if((!lat || !lng) && navigator.geolocation) {
            console.log('using browser loation');
            navigator.geolocation.getCurrentPosition(p1.initBrowserGeoCallback);
        }
        else {
            initMap(lat, lng);
        }
    };

    this.initBrowserGeoCallback = function(pos) {
        //console.log(pos);
        initMap(pos.coords.latitude, pos.coords.longitude);
    };

    var initMap = function(lat, lng) {
        //console.log("initmap ("+lat+", "+lng+")");
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
        p1.geocoder = new google.maps.Geocoder();

        google.maps.event.addListenerOnce(p1.map, 'idle', function(){
            p1.refresh();
            p1.drawGrid();

            google.maps.event.addListener(p1.map, 'dragstart', function() {
                p1.clearGrid();
                p1.dragging = true;
            });

            google.maps.event.addListener(p1.map, 'dragend', function() {
                p1.dragging = false;
                p1.refresh();
                p1.drawGrid();
            });

            google.maps.event.addListener(p1.map, 'bounds_changed', function() {
                p1.refresh();
                p1.drawGrid();
            });

            google.maps.event.addListener(p1.map, 'click', function(event) {
                p1.geocoder.geocode({'latLng': event.latLng}, function (latLng) {
                    return function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (results[1]) {
                                $("#posts__post_latitude").val(latLng.lat());
                                $("#posts__post_longitude").val(latLng.lng());
                                $("#posts__post_location").val(results[1].formatted_address);
                                $("#post_posts__form").dialog({ title: 'New Post near '+results[1].formatted_address });
                            }
                        } else {
                            alert("Geocoder failed due to: " + status);
                        }
                    };
                }(event.latLng));
            });
        });
    };

    this.refresh = function (newPostData) {
        console.dir(newPostData);
        if (p1.dragging || p1.refreshing)
            return;

        if(newPostData != null) {
            var newPostJSON = $.parseJSON(newPostData);
            if (newPostJSON.errors.length > 0) {
                p1.showError(newPostJSON.errors);
                return;
            }
        }

        p1.clearPosts();
        p1.setRefreshing();

        var swlat = p1.map.getBounds().getSouthWest().lat();
        var swlng = p1.map.getBounds().getSouthWest().lng();
        var nelat = p1.map.getBounds().getNorthEast().lat();
        var nelng = p1.map.getBounds().getNorthEast().lng();
        theGridREST.getPostsByBounds({'swlat':swlat, 'swlng':swlng, 'nelat':nelat, 'nelng':nelng, 'filter':true},
        function (data) {
            console.dir(data);
            var obj = $.parseJSON(data);

            if(obj.errors.length > 0) {
                p1.showError(obj.errors);
            }

            for(var i=0; i <obj.data.posts.length; i++) {
                var marker = new google.maps.Marker({
                    position: new google.maps.LatLng(parseFloat(obj.data.posts[i].latitude), obj.data.posts[i].longitude),
                    map: p1.map,
                    title: obj.data.posts[i].id
                });

                var markerClickHandler = function(obj, marker) {
                    return function() {
                        p1.loadPost(obj.id, marker);
                    };
                };

                google.maps.event.addListener(marker, 'click', markerClickHandler(obj.data.posts[i], marker));
                p1.markers.push(marker);
            }
        });
    };

    this.setRefreshing = function () {
        p1.refreshing = true;
        setTimeout('p1.refreshing=false;', 1000);
    };

    this.clearPosts = function () {
        for (var i = 0; i < p1.markers.length; i++) {
            p1.markers[i].setMap(null);
        }
        p1.markers.length = 0;
    };

    this.postAuthentication = function (data) {
        console.dir(data);
        var res = $.parseJSON(data);
        if(res.errors.length > 0)
            p1.showError(res.errors);
        else {
            p1.setCookie(p1.cookie_token,res.data.user.token, 30);
            p1.setCookie(p1.cookie_userId, res.data.user.id, 30);
            location.reload();
        }
    }

    this.loadPost = function (id, marker) {
        theGridREST.getPost(
                {'parentId':id},
                function (data) {
                    var obj = $.parseJSON(data);

                    if(obj.errors.length > 0) {
                        p1.showError(obj.errors);
                    }

                    var view = p1mvc.renderView('post', {
                        'parentPost':{'html':p1mvc.renderPostItem(obj.data.post)},
                        'replyPosts':{'html':''},
                        'parentId':{'attr':{'name':'value', 'value':id}}});

                    $(view).dialog({ title: 'Post: '+id, height: $(window).height(), width: 1090, resizable: false, draggable: false });
                    for(id in obj.data.posts) {
                        $(view).find("#replyPosts").append(p1mvc.renderPostItem(obj.data.posts[id]));
                    }
                });

        p1.currentPost = id;
        p1.currentPostMarker = marker;
        p1.currentPostLastRefresh = p1.getDate();
    }

    this.refreshPost = function (replyData) {
        if(replyData != null) {
            var replyDataJSON = $.parseJSON(replyData);
            if (replyDataJSON.errors.length > 0) {
                p1.showError(replyDataJSON.errors);
                return;
            }
        }

        theGridREST.getPost(
            {'parentId':p1.currentPost}, //'after':$("#replyPosts .postItem").first().attr("postId")
            function (data) {
                var obj = $.parseJSON(data);
                if(obj.errors.length > 0) {
                    p1.showError(obj.errors);
                }

                var newPosts = '<div></div>';
                $("#replyPosts").empty();
                for(id in obj.data.posts) {
                    $("#replyPosts").append(p1mvc.renderPostItem(obj.data.posts[id]));
                }
            }
        );
        p1.currentPostLastRefresh = p1.getDate();
    }

    this.getDate = function () {
        var t = new Date();
        var YYYY = t.getFullYear();
        var MM = ((t.getMonth() + 1 < 10) ? '0' : '') + (t.getMonth() + 1);
        var DD = ((t.getDate() < 10) ? '0' : '') + t.getDate();
        var HH = ((t.getHours() < 10) ? '0' : '') + t.getHours();
        var mm = ((t.getMinutes() < 10) ? '0' : '') + t.getMinutes();
        var ss = ((t.getSeconds() < 10) ? '0' : '') + t.getSeconds();
        return YYYY+'-'+MM+'-'+DD+' '+HH+':'+mm+':'+ss;
    }

    this.toggleGrid = function() {
        if($("#toggleGrid").is(":checked")) {
            p1.drawGrid();
        }
        else {
            p1.clearGrid();
        }
    };

    this.clearGrid = function() {
        for(var i=0; i<p1.gridLines.length; i++) {
            p1.gridLines[i].setMap(null);
        }
        p1.gridLines.length = 0;
    };
    
    this.drawGrid = function() {
        if (p1.dragging || !$("#toggleGrid").is(":checked"))
            return;

        p1.clearGrid();

        var swlat = p1.map.getBounds().getSouthWest().lat();
        var swlng = p1.map.getBounds().getSouthWest().lng();
        var nelat = p1.map.getBounds().getNorthEast().lat();
        var nelng = p1.map.getBounds().getNorthEast().lng();

        if(nelng > swlng) {
            p1.drawLatLine(swlat,nelat, swlng, nelng);
        }
        else {
            p1.drawLatLine(swlat,nelat, swlng, 180);
            p1.drawLatLine(swlat,nelat, -180, nelng);
        }

        if (swlng > nelng) {
            p1.drawLngLine(swlat, nelat, swlng, 180);
            p1.drawLngLine(swlat, nelat, -180, nelng);
        }
        else {
            p1.drawLngLine(swlat, nelat, swlng, nelng);
        }

        //console.log("zoom:"+p1.map.getZoom());
    };

    this.drawLatLine = function (swlat, nelat, swlng, nelng) {
        if(nelng - swlng > 180) {
            p1.drawLatLine(swlat,nelat, swlng, 0);
            p1.drawLatLine(swlat,nelat, 0, nelng);
            return;    
        }

        var latStep = (nelat - swlat) / 8;
        var currentLatLine = latStep * Math.floor(swlat / latStep);
        while (currentLatLine < nelat) {
            var gridCord = [
                            new google.maps.LatLng(currentLatLine, swlng),
                            new google.maps.LatLng(currentLatLine, nelng)
                            ];
            p1.gridLines.push(new google.maps.Polyline({
                path: gridCord,
                strokeColor: "#333333",
                strokeOpacity: 0.6,
                strokeWeight: 1,
                map: p1.map
            }));
            currentLatLine += latStep;
        }
    };

    this.drawLngLine = function (swlat, nelat, swlng, nelng) {
        var lngStep = (nelng - swlng) / 8;
        var currentLngLine = lngStep * Math.floor(swlng / lngStep);
        while (currentLngLine < nelng) {
            var gridCord = [
                            new google.maps.LatLng(swlat, currentLngLine),
                            new google.maps.LatLng(nelat, currentLngLine)
                            ];
            p1.gridLines.push(new google.maps.Polyline({
                path: gridCord,
                strokeColor: "#333333",
                strokeOpacity: 0.6,
                strokeWeight: 1,
                map: p1.map
            }));
            currentLngLine += lngStep;
        }
    };

    this.setCookie = function (c_name,value,exdays)
    {
        var exdate=new Date();
        exdate.setDate(exdate.getDate() + exdays);
        var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
        document.cookie=c_name + "=" + c_value;
        console.dir(document.cookie);
    };

    this.getCookie = function (c_name)
    {
        var i,x,y,ARRcookies=document.cookie.split(";");
        for (i=0;i<ARRcookies.length;i++)
        {
            x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
            y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
            x=x.replace(/^\s+|\s+$/g,"");
            if (x==c_name)
            {
                return unescape(y);
            }
        }
    };

    this.showError = function(errors) {
        var error_str = "";
        for (var i = 0; i < errors.length; i++) {
            error_str += "<p>"+errors[i].msg+"<br/>"+errors[i].custom_msg+"</p>";
        }
        $("<pre>"+error_str+"</pre>").dialog({ title: 'Error', width: 900 });
    }
};