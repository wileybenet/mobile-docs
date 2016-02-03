angular.module('app/mdTemplate', ['ngRoute'])

  .config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
    $locationProvider.html5Mode(false).hashPrefix('!');

    $routeProvider
      .when('/:index?/:file?', {
        template: '',
        controller: ['$scope', '$location', '$routeParams', 'Emitter',
          function($scope, $location, $routeParams, Emitter) {
            Emitter.emit('router.change', $routeParams);
            if (location.href != '' && location.href != 'http:') {
              ga('send', 'event', 'templated_pageview', location.href);
            }
          }
        ]
      });
  }])

  // error
  // http://www.winwrap.com/web2/basic#/WWB-pop__declarationgroupz_popup.htm

  .directive('mdTemplate', ['$routeParams', '$templateCache', '$http', 'Emitter', function($routeParams, $templateCache, $http, Emitter) {
    return {
      template: 
        '<div class="liner">'+
          '<div data-ng-view></div>'+
          '<div class="template-content" data-ng-include="template" data-ng-class="{loading: loading}"></div>'+
          '<div class="loader" data-ng-if="loading"><i class="fa fa-circle-o-notch fa-spin"></i></div>'+
          '<div class="template-content" data-ng-if="!template">'+
            '<span data-ng-transclude></span>'+
          '</div>'+
        '</div>',
      scope: {
        config: '=mdTemplate'
      },
      transclude: true,
      link: function(scope, element, attrs) {
        if (scope.config.float) {
          element.addClass('floated');
          element.parent().append('<div class="clear"></div>');
        }

        Emitter.on('router.change', function(data) {
          if (data.file) {
            scope.loading = true;
            if (data.file.indexOf('tpl-') === 0) {
              $http.get(MD.root + '/api/doc/' + data.file).success(function(html) {
                $templateCache.put(data.file, html);
                scope.template = data.file;
                scope.loading = false;
              });
            } else {
              $http.get(MD.root + scope.config.dir + data.file + '?index=' + data.index).success(function(html) {
                $templateCache.put(data.file, html);
                scope.template = data.file;
                scope.loading = false;
              });
            }
            $('html, body').animate({ scrollTop: 0 }, 200);
          } else {
            scope.template = false;
          }
        });

        element.attr({
          id: 'template-container'
        });
      }
    };
  }]);