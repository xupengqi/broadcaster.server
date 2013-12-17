var theGridREST = new function () {
    var appPath = '/';

    this.getPost = function (params, callback) {
        posts('GET', 'byParent', params, callback);
    };

    this.getPostsByBounds = function (params, callback) {
        posts('GET', 'byBounds', params, callback);
    };

    this.postsNewPost = function (params, callback) {
        posts('POST', '', params, callback);
    };

    this.postsReply = function (params, callback) {
        posts('POST', 'reply', params, callback);
    };

    this.accountRegister = function (params, callback) {
        account('POST', 'register', params, callback);
    };

    this.accountLogin = function (params, callback) {
        account('POST', 'login', params, callback);
    };

    this.accountLogout = function (params, callback) {
        account('POST', 'logout', params, callback);
    };

    this.postsNewPostCallback = function (event) {
        $("#posts__post_userId").val(p1.getCookie(p1.cookie_userId));
        $("#posts__post_token").val(p1.getCookie(p1.cookie_token));
        theGridREST.postsNewPost($("#post_posts__form").serialize(), p1.refresh);
        return false;
    };

    this.postsReplyCallback = function (event) {
        $("#posts_reply_post_userId").val(p1.getCookie(p1.cookie_userId));
        $("#posts_reply_post_token").val(p1.getCookie(p1.cookie_token));
        theGridREST.postsReply($("#post_posts_reply_form").serialize(), p1.refreshPost);
        return false;
    };

    this.accountRegisterCallback = function(event) {
        theGridREST.accountRegister($("#account_register_form").serialize(), p1.postAuthentication);
        return false;
    };

    this.accountLoginCallback = function(event) {
        theGridREST.accountLogin($("#account_login_form").serialize(), p1.postAuthentication);
        return false;
    };

    this.accountLogoutCallback = function(event) {
        theGridREST.accountLogout($("#account_logout_form").serialize(), theGridREST.reload);
        return false;
    };

    var posts = function(method, action, params, callback) {
        $.ajax({
            type: method,
            url: appPath+"/posts/"+action,
            data: params,
            success: function(data) {
                callback(data);
            }
        });
    };

    var account = function(method, action, params, callback) {
        $.ajax({
            type: method,
            url: appPath+"/account/"+action,
            data: params,
            success: function(data) {
                callback(data);
            }
        });
    };

    this.reload = function () {
        location.reload();
    }
};