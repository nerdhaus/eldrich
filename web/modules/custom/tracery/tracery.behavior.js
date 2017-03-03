/**
 * Click-generates grammars, yum..
 */

(function ($) {
  'use strict';

  var traceryBuilder = {};

  Drupal.behaviors.tracery = {
    attach: function (context, settings) {
      $(context).find('#traceryTrigger').click(function() {
        if (!traceryBuilder.hasOwnProperty('grammar')) {
          loadjs([drupalSettings.tracery.grammar], 'compileGrammar');

          loadjs.ready('compileGrammar', {
            success: function() {
              traceryBuilder.grammar = tracery.createGrammar(grammar);
            }
          });
        }

        if (typeof traceryBuilder.grammar !== 'undefined') {
          var output = traceryBuilder.grammar.flatten("#NAME#");
          $(context).find('.traceryDomTarget').text(output);
          $(context).find('.traceryInputTarget').val(output);
        }
      });
    }
  };

}(jQuery));
