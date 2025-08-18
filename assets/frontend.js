jQuery(document).ready(function($){
    let selectedCat = 0;
    let selectedSub = 0;

    $('#bpi-category-toggle').on('click', function(){
        $('.bpi-category-dropdown').toggleClass('open');
    });

    $('.bpi-cat-item').on('click', function(e){
        selectedCat = $(this).data('id');
        selectedSub = 0;
        $('.bpi-cat-item, .bpi-sub-item').removeClass('selected');
        $(this).addClass('selected');
        $('.bpi-category-dropdown').removeClass('open');
        e.stopPropagation();
    });

    $('.bpi-sub-item').on('click', function(e){
        selectedCat = $(this).closest('.bpi-cat-item').data('id');
        selectedSub = $(this).data('id');
        $('.bpi-sub-item').removeClass('selected');
        $(this).addClass('selected');
        $('.bpi-category-dropdown').removeClass('open');
        e.stopPropagation();
    });

    function bindModal(){
        const modal = $('#bpi-modal');
        const modalBody = modal.find('.bpi-modal-body');
        $('.bpi-result-card').off('click').on('click', function(){
            modalBody.html($(this).find('.bpi-card-details').html());
            modal.addClass('open');
        });
        modal.off('click').on('click', function(e){
            if($(e.target).hasClass('bpi-close') || e.target === this){
                modal.removeClass('open');
            }
        });
    }

    $('#bpi-live-search').on('input', function(){
        const term = $(this).val();
        if(term.length < 3){
            $('#bpi-live-results').empty();
            return;
        }
        $.post(bpiAjax.ajax_url, {
            action: 'bpi_live_search',
            keyword: term,
            cat: selectedCat,
            sub: selectedSub
        }, function(response){
            $('#bpi-live-results').html(response);
            bindModal();
        });
    });

    bindModal();
});
