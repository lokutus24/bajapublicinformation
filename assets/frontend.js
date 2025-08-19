jQuery(document).ready(function($){
  let selectedCat = 0;
  let selectedSub = 0;

  const $dropdown = $('.bpi-category-dropdown');
  const $list = $('.bpi-category-list');
  const $selectedMain = $('#bpi-selected-main');
  const $selectedSub = $('#bpi-selected-sub');

  // Toggle gomb
  $('#bpi-category-toggle').on('click', function(e){
    e.stopPropagation();
    const isOpen = $dropdown.hasClass('open');
    if (isOpen) {
      $dropdown.removeClass('open');
      $list.stop(true, true).slideUp(150);
    } else {
      $dropdown.addClass('open');
      $list.stop(true, true).slideDown(150);
    }
  });

  // Kattintás a dropdownon belül ne buborékoljon fel
  $dropdown.on('click', function(e){ e.stopPropagation(); });

  // Kívülre kattintásra zár
  $(document).on('click', function(){
    if ($dropdown.hasClass('open')) {
      $dropdown.removeClass('open');
      $list.stop(true, true).slideUp(150);
    }
  });

  // ESC-re zár
  $(document).on('keydown', function(e){
    if (e.key === 'Escape' && $dropdown.hasClass('open')) {
      $dropdown.removeClass('open');
      $list.stop(true, true).slideUp(150);
    }
  });

  // Kategória választás
  function isMobile(){ return window.matchMedia('(max-width: 768px)').matches; }

  // Megakadályozzuk, hogy mobilon a fő katt zárja a teljes dropdownt
  $('.bpi-cat-item').on('click', function(e){
    if ($(e.target).closest('.bpi-sub-list').length){
      return;
    }
    e.stopPropagation();
    selectedCat = $(this).data('id');
    selectedSub = 0;
    $('.bpi-cat-item').removeClass('selected');
    $('.bpi-sub-item').removeClass('selected');
    $(this).addClass('selected');
    $selectedMain.text('Kategória: ' + $(this).data('name'));
    $selectedSub.hide().empty();

    if (isMobile()){
      $(this).toggleClass('open')
             .siblings('.bpi-cat-item').removeClass('open');
    } else {
      $dropdown.removeClass('open');
      $list.stop(true, true).slideUp(150);
    }
  });

  // Ablakméret váltáskor tisztítsuk az állapotot
  $(window).on('resize', function(){
    if (!isMobile()){
      $('.bpi-cat-item').removeClass('open');
    }
  });

  // Alkategória választás
  $('.bpi-sub-item').on('click', function(e){
    e.stopPropagation();
    const $cat = $(this).closest('.bpi-cat-item');
    selectedCat = $cat.data('id');
    selectedSub = $(this).data('id');
    $('.bpi-sub-item').removeClass('selected');
    $('.bpi-cat-item').removeClass('selected');
    $(this).addClass('selected');
    $cat.addClass('selected');
    $selectedMain.text('Kategória: ' + $cat.data('name'));
    $selectedSub.html($(this).data('name') + ' <span class="bpi-remove-sub">&times;</span>').show();

    $dropdown.removeClass('open');
    $list.stop(true, true).slideUp(150);
  });

  $('.bpi-category-list > .bpi-item-default').on('click', function(e){
    e.stopPropagation();
    selectedCat = 0;
    selectedSub = 0;
    $('.bpi-cat-item, .bpi-sub-item').removeClass('selected');
    $selectedMain.text('Kategória');
    $selectedSub.hide().empty();
    $dropdown.removeClass('open');
    $list.stop(true, true).slideUp(150);
  });

  $('.bpi-sub-list > .bpi-item-default').on('click', function(e){
    e.stopPropagation();
    const $cat = $(this).closest('.bpi-cat-item');
    selectedCat = $cat.data('id');
    selectedSub = 0;
    $('.bpi-cat-item').removeClass('selected');
    $('.bpi-sub-item').removeClass('selected');
    $cat.addClass('selected');
    $selectedMain.text('Kategória: ' + $cat.data('name'));
    $selectedSub.hide().empty();
    $dropdown.removeClass('open');
    $list.stop(true, true).slideUp(150);
  });

  $selectedSub.on('click', '.bpi-remove-sub', function(e){
    e.stopPropagation();
    selectedSub = 0;
    $('.bpi-sub-item').removeClass('selected');
    $selectedSub.hide().empty();
  });

  // --- MODÁL ---
  function bindModal(){
    const $modal = $('#bpi-modal');
    const $modalBody = $modal.find('.bpi-modal-body');

    $('.bpi-result-card').off('click').on('click', function(){
      $modalBody.html($(this).find('.bpi-card-details').html());
      $modal.addClass('open');
    });

    $modal.off('click').on('click', function(e){
      if($(e.target).hasClass('bpi-close') || e.target === this){
        $modal.removeClass('open');
      }
    });
  }

  // Live search
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
