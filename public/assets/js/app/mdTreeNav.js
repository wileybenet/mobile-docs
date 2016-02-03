angular.module('app/mdTreeNav', ['ngResource', 'ngSanitize'])

  .run(['$templateCache', function ($templateCache) {
    $templateCache.put('tree-nav-el',
      '<div class="row" data-ng-class="{\'expand\': file.$current, \'tr\': file.$current.length, \'fixed\': file.$current.length}">'+
        '<div class="leader">'+
          '<a href="#!/{{config.index}}/{{file.link}}" data-ng-bind-html="validate(file.name)" data-is-selected="[0,1]"></a>'+
        '</div>'+
      '</div>'+
      '<div class="body" data-ng-include="\'tree-nav-el\'" data-ng-repeat="file in file.$current"></div>');
  }])

  .directive('mdTreeNav', ['$resource', '$sce', '$timeout', 'Emitter', function($resource, $sce, $timeout, Emitter) {
    return {
      template: 
        '<div class="top">'+
          '<div class="row" data-ng-class="{\'expand\': file.$current, \'tr\': file.$current.length, \'fixed\': file.$current.length}">'+
            '<div class="leader">'+
              '<a data-ng-href="#!/" data-is-selected data-ng-bind-html="validate(config.home)"></a>'+
            '</div>'+
          '</div>'+
        '</div>'+
        '<div class="body" data-ng-include="\'tree-nav-el\'" data-ng-repeat="file in tree.children"></div>'+
        '<div class="loader" style="background: transparent;" data-ng-if="!loader.$resolved">'+
          '<i class="center fa fa-circle-o-notch fa-spin"></i>'+
        '</div>',
      scope: {
        config: '=mdTreeNav'
      },
      link: function(scope, element, attrs) {
        var model = $resource(MD.root + scope.config.dir + ':id', {}, {}),
          currentEvt, currentFile;

        function treePush(newFile, pointer) {
          if (pointer.id === newFile.parent_id) {
            pointer.children = pointer.children || [];
            pointer.children.push(newFile);
          } else if (pointer.children) {
            pointer.children.forEach(function(child) {
              treePush(newFile, child);
            });
          }
        }

        function resetTree() {
          scope.files.forEach(function(file) {
            file.$holder = file.children;
            file.$current = file.children ? [] : null;
          });
        }

        function climb(file) {
          file.$current = file.$holder;
          if (file.parent_id) {
            climb(scope.files.findWhere({ id: file.parent_id }));
          }
        }

        function addToTree(file) {
          treePush(file, scope.tree);
        }

        function findCurrentFile(evt) {
          if ((evt.file && scope.files) || currentEvt && currentEvt.file) {
            currentFile = scope.files.findWhere({ link: evt.file || currentEvt.file });
            $timeout(function() {
              resetTree();
              climb(currentFile);
            });
          } else {
            scope.files && resetTree();
            currentEvt = evt;
          }
        }

        scope.loader = model.query(function(data) {
          scope.files = data

          findCurrentFile({});

          scope.tree = { id: 0, children: [] };

          scope.files.forEach(addToTree);

          resetTree();

        });

        Emitter.on('router.change', findCurrentFile);

        scope.validate = function(str) {
          return $sce.trustAsHtml(str);
        };

        element.attr({
          id: 'reference-nav'
        });
      }
    };
  }]);