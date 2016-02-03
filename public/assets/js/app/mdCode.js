angular.module('app/mdCode', ['ngAce'])

  .directive('mdCode', [function() {
    var uid = 0;

    return {
      template: 
        '<div class="code" data-ng-transclude></div>',
      transclude: true,
      scope: {
        config: '=mdCode',
      },
      link: function(scope, element, attrs) {
        var id ='highlighted-' + uid++,
          $el = element.find('.code'),
          code = $el.find('span').text();
        $el.attr({
          id: id
        });

        var lang = scope.config.lang;
        var style = scope.config.style || 'wwbnet';
        if (style == 'C++') {
          style = 'csharp';
        }

        editor = ace.edit(id);
        editor.setTheme('ace/theme/' + style);
        editor.setOptions({
          maxLines: scope.config.lines || 30,
          readOnly: true,
          fontSize: 14
        });

        editor.setHighlightActiveLine(false);
        editor.setWrapBehavioursEnabled(true);

        session = editor.getSession();
        session.setMode('ace/mode/' +lang);
        session.setTabSize(2);

        session.setValue(code);

        element.addClass('highlighted-code');
      }
    };
  }]);