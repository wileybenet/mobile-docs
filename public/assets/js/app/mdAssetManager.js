angular.module('app/mdAssetManager', ['app/mdNavigator', 'app/mdEditor', 'ngRoute', 'ngResource'])

  // Asset manager for admin view
  // instantiates resource descriptions for the given asset name
  // has full CRUD operations for all objects in the collection
  //   including versioning for the `content` field, and file asset uploads
  .directive('mdAssetManager', ['$resource', '$routeParams', '$timeout', 'Emitter',

    function($resource, $routeParams, $timeout, Emitter) {
      return {
        template: [
          // '<div>',
          //   '<div id="assets" class="panel"></div>',
          // '</div>'
          '<h3 class="dark">',
            '<i class="fa" data-ng-class="{\'fa-angle-down\': !collapsed, \'fa-angle-right\': collapsed}" data-ng-click="collapsed = !collapsed"></i> ',
            'Assets',
          '</h3>',
          '<div class="screen screen-full" data-ng-if="previewFile" data-ng-click="closePreview()">',
          '</div>',
          '<div class="preview" data-ng-if="previewFile" data-ng-click="closePreview()">',
            '<div class="preview-title" data-stop-prop>',
              '<input data-ng-model="previewFile" data-ng-if="editing" />',
              '<span data-ng-if="!editing">{{previewFile}}</span>',
              '<div class="actions">',
                '<button class="btn blank" data-ng-click="editing = true">Edit</button>',
                '<button class="btn blank stop" data-ng-click="delete(previewFile)">Delete</button>',
              '</div>',
            '</div>',
            '<img data-ng-src="{{base}}/public/asset/{{type}}/{{name}}/{{previewFile}}?preview=1" />',
          '</div>',
          '<div class="upload-form" data-ng-if="!reset" data-ng-hide="collapsed">',
            '<h4>Upload Assets</h4>',
            '<form name="file_upload" class="form-file" role="form" method="POST" action="{{action}}" enctype="multipart/form-data" novalidate>',
              '<button class="browse btn info blank"><i class="fa fa-folder"></i> Browse</button>',
              '<ul class="file-tray">',
                '<li data-ng-repeat="file in uploadFiles">',
                  '<span>{{file.name}}</span>',
                '</li>',
              '</ul>',
              '<input name="loc" type="hidden" value="{{location}}" />',
              '<input class="hidden-file-input" multiple="multiple" type="file" name="file[]" onchange="angular.element(this).scope().fileChange(this)">',
              '<button type="submit" class="submit btn" data-ng-disabled="!uploadFiles">',
                'Upload',
              '</button>',
            '</form>',
          '</div>',

          '<div class="thumbnails" data-ng-hide="collapsed">', 
            '<div class="thumbnail" data-ng-repeat="file in thumbnails" data-ng-click="preview(file)">',
              '<img data-ng-src="{{base}}/asset/{{type}}/{{name}}/{{file}}?preview=1" />',
              '<div class="caption">{{file}}</div>',
            '</div>',
            '<div class="title" data-ng-if="!thumbnails.length">Assets</div>',
          '</div>',

          '<div style="clear: both;"></div>'
        ].join(''),
        scope: {
          data: '=mdAssetManager'
        },
        link: function(scope, element, attrs) {
          var assetModel = $resource(MD.root + '/api/' + scope.data.type + '/:name/asset', {
            id: '@id'
          });

          scope.base = MD.root;
          scope.type = scope.data.type;

          function getAssets(model) {
            scope.name = scope.data.name || model.name;
            scope.thumbnails = assetModel.query({ name: scope.name });
            scope.action = MD.root + '/api/' + scope.type + '/' + scope.name + '/asset';
            scope.location = location.href;
            $timeout(function() {
              scope.reset = false;
            });
          }

          scope.preview = function(file) {
            scope.previewFile = file;
          };

          scope.closePreview = function() {
            scope.previewFile = null;
          };

          scope.fileChange = function(element) {
            scope.$apply(function() {
              scope.uploadFiles = [].slice.call(element.files, 0);
            });
          };

          Emitter.on('navigator.change', function(currentItem, originalEvent) {
            scope.reset = true;
            getAssets(currentItem);
          });

          element.attr({id: 'assets'});
        }
      };
    }
  ]);