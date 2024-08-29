jQuery(document).ready(function($) {
    // Gửi yêu cầu tìm kiếm AJAX
    $('#mtips5s-search').on('input', function() {
        var searchQuery = $(this).val();

        $.ajax({
            url: mtips5sAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'mtips5s_search',
                nonce: mtips5sAjax.nonce,
                search_query: searchQuery
            },
            success: function(response) {
                if (response.success) {
                    $('#mtips5s-posts-container').html(response.data.html);
                }
            }
        });
    });

    // Sắp xếp bằng AJAX
    $('.mtips5s-sort').on('click', function(e) {
        e.preventDefault();
        var orderby = $(this).data('orderby');

        $.ajax({
            url: mtips5sAjax.ajax_url,
            type: 'GET',
            data: {
                action: 'mtips5s_search',
                nonce: mtips5sAjax.nonce,
                search_query: $('#mtips5s-search').val(),
                orderby: orderby
            },
            success: function(response) {
                if (response.success) {
                    $('#mtips5s-posts-container').html(response.data.html);
                }
            }
        });
    });
});
