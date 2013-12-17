var p1mvc = new function () {
    this.views = {};

    this.loadView = function (name, html) {
        p1mvc.views[name] = html;
    };

    this.getView = function () {
        //get view from server
        $html = '';
        p1mvc.loadView($html); 
    };

    this.renderView = function (name, vars, attributes) {
        var template = $(p1mvc.views[name]).clone();
        for(attr in vars) {
            for(replace in vars[attr]) {
                var toReplace = $(template).find("[p1mvc="+attr+"]");
                if(toReplace.length == 0)
                    toReplace = template;
                switch(replace) {
                case 'html':
                    if (typeof vars[attr][replace] === 'string') {
                        vars[attr][replace] = unescape(JSON.parse('"' + vars[attr][replace] + '"'))
                    }
                    //console.log("attr:"+attr+",replace:"+vars[attr][replace]);
                    $(toReplace).html(vars[attr][replace]);
                    break;
                case 'attr':
                    //console.log("attr:"+attr+",attr:"+vars[attr][replace].name+",replace:"+vars[attr][replace].value);
                    $(toReplace).attr(vars[attr][replace].name, vars[attr][replace].value);
                    break;
                }
            }
        }
        for(a in attributes) {
            $(template).attr(a, attributes[a]);
        }
        return template;
    };

    this.renderPostItem = function (postItem) {
        return p1mvc.renderView('postItem', {
            'postItemUser': {'html':postItem['username']},
            'postItemText': {'html':postItem['title']+"<br/>"+postItem['content']['text']},
            'postItemDate': {'html':postItem['created']}},
            {'postId':postItem['id']});
    };
};