angular.module('app/mdModal', [])
  
  .directive('mdModal', ['$http', '$cacheFactory', function($http, $cacheFactory) {
    return {
      scope: {
        src: '=mdModal'
      },
      link: function(scope, element, attrs) {
        var content = '',
          cache = $cacheFactory('modal');

        if (scope.src) {
          if (!(content = cache.get(scope.src))) {
            $http.get(MD.root + scope.src).success(function(data) {
              cache.put(scope.src, content = data);
            });
          }
        }

        element.on('click', function() {
          var $modal = $('<div class="modal">'+
              '<div class="window">'+
                '<i class="fa fa-remove close"></i>'+
                content+
              '</div>'+
            '</div>');
          $('body').append($modal);

          $modal.find('.close').on('click', function() {
            $modal.remove();
          });
        });
      }
    };
  }]);