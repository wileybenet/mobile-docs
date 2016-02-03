angular.module('app/mdNavigator', ['ngRoute', 'ngResource', 'ngSanitize'])

  // 
  .config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
    $locationProvider.html5Mode(false).hashPrefix('!');

    $routeProvider
      .when('/:name?', {
        template: [
          '<h1 data-ng-bind-html="title"></h1>'
        ].join(''),
        controller: ['$scope', '$location', '$routeParams', '$timeout', '$sce', 'Emitter',
          function($scope, $location, $routeParams, $timeout, $sce, Emitter) {
            if (!$routeParams.name) {
              $location.path();
            }

            $scope.title = '-';

            var unbind = Emitter.on('set.title', function(data) {
              $scope.title = $sce.trustAsHtml(data.title);
            });

            Emitter.emit('router.change', $routeParams);

            $scope.$on('$destroy', unbind);
          }
        ]
      });
  }])

  // 
  .directive('mdNavigator', ['$resource', '$location', 'Emitter',
    function($resource, $location, Emitter) {
      return {
        template: 
          '<div id="editor-nav-optns" class="optn-vertical">'+
            '<div class="editor-nav-optns-wrapper">'+
              '<a data-ng-href="#!/{{item._name}}" data-is-selected="[0, 1]" class="editor-selector optn" data-ng-repeat="item in items" data-ng-bind="item._title"></a>'+
              '<i class="fa fa-circle-o-notch center fa-spin" data-ng-if="!items.length"></i>'+
            '</div>'+
            '<div class="actions">'+
              '<input placeholder="* Title" data-ng-model="newTitle" data-ng-hide="!create" />'+
              '<input placeholder="* Name" data-ng-model="newName" data-ng-hide="!create" />'+
              '<button class="btn blank half small" data-ng-click="create = false" data-ng-hide="!create">Cancel</button>'+
              '<button class="btn go blank half small" data-ng-click="createNew(newTitle, newName, newType)" data-ng-hide="!create">Create</button>'+
              '<button class="btn go blank" data-ng-click="create = true" data-ng-hide="create">New</button>'+
            '</div>'+
          '</div>',
        scope: {
          
          // Config options: 
          //   endpoint:      uri
          //   name:          item name,
          //   title:         item display title

          config: '=mdNavigator'
        },
        link: function(scope, element, attrs) {
          var model = $resource(MD.root + '/api/' + scope.config.type + '/:id', {
              id: '@id'
            }, {
              update: {
                method: 'PUT',
                transformResponse: function(json) {
                  var data = JSON.parse(json),
                    item = scope.items.findWhere({ id: data.id });
                  for (var key in data) {
                    item[key] = data[key];
                  }
                  return data;
                }
              }
            }),
            currentItem,
            routeData;

          function route(data) {
            if (!scope.items || !scope.items.length)
              return routeData = data;

            currentItem = scope.items.findWhere({ _name: data.name });

            Emitter.emit('navigator.change', currentItem);
          }

          function format(item) {
            item._name = item[scope.config.name];
            item._title = item[scope.config.title];
            item._type = item._class.toLowerCase();
          }

          scope.newTitle = '';
          scope.newName = '';
          scope.create = false;

          scope.createNew = function(title, name, type) {
            model.save({
              title: title, 
              name: name.toLowerCase().replace(/[^a-z0-9\-]/g, '')
            }, function(data) {
              format(data);
              scope.items.push(data);
              scope.newTitle = '';
              scope.newName = '';
              scope.create = false;
            });
          };

          element.attr({ id: 'editor-nav' });

          Emitter.on('router.change', route);

          model.query(function(items) {
            scope.items = items.filter(function(item) {
              return item.hasOwnProperty('edit') ? +item.edit : true;
            }).sortBy(function(item) { return item[scope.config.title].toLowerCase(); });

            scope.items.forEach(format);

            if (routeData.name) {
              route(routeData);
            } else {
              $location.path('/' + scope.items[0]._name);
              $location.replace();
            }
          });
        }
      }
    }
  ]);