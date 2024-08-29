jQuery(document).ready(function($) {
    $('.mtips5s-toggle').on('click', function() {
        var view = $(this).data('view');
        $('.mtips5s-posts').removeClass('mtips5s-list-view mtips5s-grid-view').addClass('mtips5s-' + view + '-view');
    });
});
