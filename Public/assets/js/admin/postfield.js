(function($) {
    update_relationship_values = function(field) {
        var post_ids = [];
        field.find('.selected_posts div').each(function(idx) {
            post_ids[idx] = $(this).attr('rel');
        });
        field.find('input.relationship').val(post_ids.join(','));
    }

    fill_relationship_data = function(_this) {
        var modelid = _this.find('.filter_posts .model_id').val();
        var title = _this.find('.filter_posts .cfs_filter_input').val();
        var available_posts = _this.find('.available_posts');
        var selected_posts = _this.siblings('.relationship').val().split(',');
        selected_posts = $.isArray(selected_posts) ? selected_posts : [];
        $.ajax({
            url : window.post_get_posts,
            method : 'post',
            data : {'modelid' : modelid, 'title' : title, 'selected_posts' : $selected_posts},
            dataType : 'json',
            success: function(res) {
                var posts = '';
                if (res.code == 0) {
                    $.each(res.data.available_posts, function(index, ele) {
                        if ($.inArray(ele.id, selected_posts) == -1) {
                            posts += '<div rel="'+ ele.id +'">'+ ele.title +'</div>'
                        } else {
                            posts += '<div rel="'+ ele.id +'" class="used">'+ ele.title +'</div>'
                        }
                    });
                    available_posts.html(posts);
                } else {
                    available_posts.html('暂无内容！');
                }
            },
            error: function() {
                available_posts.html('暂无内容！');
            }
        });
    }

    $(function() {
        var post_timer = null;

        $('.cfs_relationship').init_relationship();
        // add selected post
        $(document).on('click', '.cfs_relationship .available_posts div', function() {
            var parent = $(this).closest('.field');
            var post_id = $(this).attr('rel');
            var html = $(this).html();
            $(this).addClass('used');
            parent.find('.selected_posts').append('<div rel="'+post_id+'"><span class="remove"></span>'+html+'</div>');
            update_relationship_values(parent);
        });

        // remove selected post
        $(document).on('click', '.cfs_relationship .selected_posts .remove', function() {
            var div = $(this).parent();
            var parent = div.closest('.field');
            var post_id = div.attr('rel');
            parent.find('.available_posts div[rel='+post_id+']').removeClass('used');
            div.remove();
            update_relationship_values(parent);
        });

        // filter posts
        $(document).on('keyup', '.cfs_relationship .cfs_filter_input', function() {
            _this = $(this);
            if (post_timer) {
                clearTimeout(post_timer);
            }
            post_timer = setTimeout(function() {
                fill_relationship_data(_this.closest('.cfs_relationship'));
            }, 600);
        });
    });

    $.fn.init_relationship = function() {
        this.each(function() {
            var $this = $(this);
            $this.addClass('ready');
            fill_relationship_data($this);
            // sortable
            $this.find('.selected_posts').sortable({
                axis: 'y',
                update: function(event, ui) {
                    var parent = $(this).closest('.field');
                    update_relationship_values(parent);
                }
            });
        });
    }
})(jQuery);