angular.module('app/mdGallery', ['ngRoute'])

  .config(['$routeProvider', function($routeProvider) {
    $routeProvider
      .when('/:image?', {
        template: [
          ''
        ].join(''),
        controller: ['$scope', '$location', '$routeParams', '$timeout', '$sce', 'Emitter',
          function($scope, $location, $routeParams, $timeout, $sce, Emitter) {
            if (!$routeParams.name) {
              $location.path();
            }

            Emitter.emit('router.change', $routeParams);
          }
        ]
      });
  }])

  .directive('mdGallery', ['$location', 'Emitter', function($location, Emitter) {
    return {
      template: 
        '<a data-ng-href="#/{{img.src}}" class="image" data-ng-style="{\'background-image\': \'url(\' + src(img.src) + \')\'}" '+
          'data-ng-repeat="img in images" '+
          'data-ng-click="select(img, $element)">'+
        '</a>'+
        '<div class="clear"></div>'+
        '<div class="view" data-ng-show="current">'+
          '<div class="screen"></div>'+
          '<a href="#/" class="close">&times;</a>'+
          '<div class="container" data-ng-click="close()">'+
            '<div class="content">'+
              '<img src="{{src(current)}}" data-stop-prop />'+
              '<i class="fa fa-angle-right right" data-ng-click="right()" data-stop-prop></i>'+
              '<i class="fa fa-angle-left left" data-ng-click="left()" data-stop-prop></i>'+
            '</div>'+
          '</div>'+
        '</div>'+
        '<div data-ng-view></div>',
      scope: {
        config: '=mdGallery',
        images: '='
      },
      link: function(scope, element, attrs) {
        scope.src = function(src) {
          return MD.root + '/asset/' + scope.config.type + '/' + scope.config.name + '/' + src;
        };

        scope.close = function() {
          $location.path('');
        };

        scope.right = function() {
          var index = scope.images.findIndex(function(img) {
              return img.src === scope.current;
            }),
            nextIndex = index + 1;

          if (nextIndex === scope.images.length) {
            nextIndex = 0;
          }

          $location.path('/' + scope.images[nextIndex].src);
        };

        scope.left = function() {
          var index = scope.images.findIndex(function(img) {
              return img.src === scope.current;
            }),
            nextIndex = index - 1;

          if (nextIndex < 0) {
            nextIndex = scope.images.length - 1;
          }

          $location.path('/' + scope.images[nextIndex].src);
        };

        function select(data) {
          scope.current = data.image;
        }

        Emitter.on('router.change', select);

        element.addClass('gallery');
      }
    };
  }]);