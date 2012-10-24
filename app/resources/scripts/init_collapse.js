/*
 * add an "active" class to open accordion titles
 **/
$(function() {
    $('.accordion').on('show', function (e) {
         $(e.target).prev('.accordion-heading').find('.accordion-toggle').addClass('active');
    });
    $('.accordion').on('hide', function (e) {
       // $(this).find('.accordion-toggle').not($(e.target)).removeClass('active');
       $(e.target).prev('.accordion-heading').find('.accordion-toggle').removeClass('active');
    });
});​
