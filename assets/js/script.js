jQuery(document).ready(function($) {
    $('.mtips5s-toggle').on('click', function() {
        var view = $(this).data('view');
        $('.mtips5s-posts').removeClass('mtips5s-list-view mtips5s-grid-view').addClass('mtips5s-' + view + '-view');
    });
});


jQuery(document).ready(function($) {
    // Hàm debounce
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Hàm xử lý tìm kiếm
    function handleSearch() {
        var $form = $('#mtips5s-search-form');
        var formData = $form.serialize();
        
        $.ajax({
            url: mtips5sAjax.ajax_url,
            type: 'GET',
            data: formData + '&action=mtips5s_search',
            success: function(response) {
                $('.mtips5s-posts').html(response);
            },
            error: function(xhr, status, error) {
                console.error('AJAX error: ' + status + ' - ' + error);
            }
        });
    }

    // Sử dụng debounce cho ô tìm kiếm
    $('#mtips5s-search-form input').on('input', debounce(handleSearch, 300));
});

