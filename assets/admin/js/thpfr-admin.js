var thpfqw_admin = (function($, window, document) {
      'use strict';
    $(document).ready(function() {
      clickable_label_for_switch();
    });

    function clickable_label_for_switch() {
        $("[data-labelfor]").click(function() {
            $('#' + $(this).attr("data-labelfor")).prop('checked',
            function(i, oldVal) { return !oldVal; });
        });
    }
     
    $("body.post-type-feature-requests #publish").click(function(event) {
        var valid = validate_frw_post();
        return valid;
    });

    function validate_frw_post() {
        var valid = true;
        var fiture_titles = $(".post-type-feature-requests #title").val().trim();
        
        if (!fiture_titles.length) {
            alert('Feature requests cannot be published without a title.');
            return false;
        }
    }

}(window.jQuery, window, document));