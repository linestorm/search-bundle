
define(['jquery', 'select2', 'cms_api'], function ($, select2, api) {

    $(document).ready(function(){
        var $search = $('input.linestorm-search'),
            $spinner = $('.search-spinner'),
            search = $search[0],
            $results = $('.search-results'),
            url = $search.data('url'),
            lastRequest;

        var delay = (function(){
            var timer = 0;
            return function(callback, ms){
                clearTimeout (timer);
                timer = setTimeout(callback, ms);
            };
        })();

        $search.on('keyup', function(){
            delay.call(this, function(){
                if(search.value.length >= 3){
                    $spinner.show();
                    lastRequest = search.value;
                    api.call(url, {
                        data: { q: search.value },
                        success: function(o){
                            if(lastRequest === search.value){
                                $results.empty();
                                var post;
                                if(o.length){
                                    $results.show();
                                    for(var i=0 ; i<o.length ; ++i){
                                        post = o[i];
                                        var html =  '<h4><a href="'+ post.data_url +'">'+post.title+'</a></h4>';
                                        if(post.blurb){
                                            html += '<p>'+post.blurb+'</p>';
                                        }

                                        html += '<a href="#" class="label label-success">'+post.category.name+'</a>';

                                        for(var j=0 ; j < post.tags.length ; ++j){
                                            html += '<a href="#" class="label label-primary">'+post.tags[j].name+'</a>';
                                        }
                                        html += '';

                                        $results.append('<div class="results-row">'+html+'</div>');
                                    }
                                    $spinner.hide();
                                } else {
                                    $results.fadeOut();
                                    $spinner.hide();
                                }
                            }
                        },
                        error: function(){
                            $results.fadeOut();
                            $spinner.hide();
                        }
                    });
                } else {
                    $spinner.hide();
                    $results.empty().hide();
                }
            }, 500 );

        }).on('blur', function(){
            setTimeout(function(){
                $results.fadeOut();
                $spinner.hide();
            }, 500);
        }).on('focus', function(){
            if($results.children().length)
                $results.show();
            $spinner.hide();
        });
    });

});
