/**
 * @file
 * Handles hiding and showing table elements.
 */
(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.toggleSkills = {
    attach: function (context) {
      var $context = $(context);

      $('.skills:input:checkbox').change(function(){
        if(this.checked)
          $("tr[data-category='" + $(this).attr("value") + "']").fadeIn('fast');
        else
          $("tr[data-category='" + $(this).attr("value") + "']").fadeOut('fast');
      });

      $('.players:input:checkbox').change(function(){
        if(this.checked) {
          $("th[data-column='" + $(this).attr("value") + "']").fadeIn('fast');
          $("td[data-column='" + $(this).attr("value") + "']").fadeIn('fast');
        }
        else {
          $("th[data-column='" + $(this).attr("value") + "']").fadeOut('fast');
          $("td[data-column='" + $(this).attr("value") + "']").fadeOut('fast');
        }
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
