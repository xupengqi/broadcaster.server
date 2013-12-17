var p1gen = new function () {
    var running = false;
    var stats = {'cycleTime': 1000, 'iterationTime': 60000, 'totalRunningTime': 0};
    var locations = [
                     {
                         'name':'San Jose',
                         'lat':37.335224,
                         'lng':-121.911163,
                         'r': 10000
                     }
                     ];

    this.init = function () {
        $("#P1Gen_start").live('click', function () { p1gen.start(); return false; });
        $("#P1Gen_stop").live('click', function () { p1gen.stop(); return false; });
        setInterval('p1gen.run();', stats['cycleTime']);
    };

    this.start = function () {
        this.clearStats();
        console.clear();
        console.log('Started.');
        running = true;
    };

    this.stop = function () {
        console.log('Stopped. Was running for: '+Math.floor(stats['totalRunningTime']/60000)+' minutes and '+(stats['totalRunningTime']%60000/1000)+' seconds');
        console.log(stats);
        running = false;
    };

    this.run = function() {
        if(running) {
            stats['totalRunningTime'] += stats['cycleTime'];
            var timeRegister = Math.floor(stats['iterationTime']/$("#P1Gen_registration").val());
            if (stats['totalRunningTime']%timeRegister == 0)
                this.register();

            var timePost = Math.floor(stats['iterationTime']/$("#P1Gen_post").val());
            if (stats['totalRunningTime']%timePost == 0)
                this.post();

            var timeReply = Math.floor(stats['iterationTime']/$("#P1Gen_reply").val());
            if (stats['totalRunningTime']%timeReply == 0)
                this.reply();
        }
    };

    this.register = function () {
        var ticks = (new Date()).getTime();
        this.addToStats('register', 1);

        var username = 'user'+ticks;
        var email = ticks+'@test.com';

        $.ajax({type: "POST", url: "/p1/account/register", data: {'data':{'email':email,'password':'test','username':username}}, success: function (data) {
            console.log('user '+ticks);
        }});
    };

    this.post = function () {
        var ticks = (new Date()).getTime();
        this.addToStats('post', 1);

        var title = 'title:'+ticks;
        var text = 'test:'+ticks;

        var bearing = Math.floor((Math.random()*360)+1); //bearing random 1 to 360
        var meters = Math.floor(Math.random()*locations[0].r); //radius in meters rand om from 0 to max
        var pointB = p1gen.dp(locations[0].lat, locations[0].lng, bearing, meters);
        //console.log(pointB);

        $.ajax({type: "POST", url: "/p1/test/post", data: {'data':{'title':title,'text':text,'visibility':'0','privacy':3, 'latitude':pointB.lat(), 'longitude':pointB.lng()}}, success: function (data) {
            console.log('post '+ticks);
            //console.log(data);
        }});
    };

    this.reply = function () {
        var ticks = (new Date()).getTime();
        this.addToStats('reply', 1);

        var title = 'test:'+ticks;

        $.ajax({type: "POST", url: "/p1/test/reply", data: {'data':{'title':title}}, success: function (data) {
            console.log('reply '+ticks);
        }});
    };

    this.addToStats = function (name, amount) {
        if(stats[name] == undefined) {
            stats[name] = 0;
        }
        stats[name] += amount;
    };

    this.clearStats = function () {
        for (i in stats) {
            switch(i) {
            case 'cycleTime':
            case 'iterationTime':
                break;
            default:
                stats[i] = 0;
            break;
            }
        }
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


$(document).ready(function () { p1gen.init(); });

Number.prototype.toRad = function() {
    return this * Math.PI / 180;
}

Number.prototype.toDeg = function() {
    return this * 180 / Math.PI;
}