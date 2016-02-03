angular.module('app/mdEditor', ['ngResource', 'ngAce'])
  
  // 
  .directive('mdEditor', ['$resource', '$cacheFactory', '$timeout', 'Emitter',
    function($resource, $cacheFactory, $timeout, Emitter) {
      var editor, session;

      return {
        template: [
          '<h3 class="dark">',
            '<i class="fa" data-ng-class="{\'fa-angle-down\': !infoCollapsed, \'fa-angle-right\': infoCollapsed}" data-ng-click="infoCollapsed = !infoCollapsed"></i> ',
            'Info',
          '</h3>',
          '<div id="meta" class="panel" data-ng-hide="infoCollapsed">', 
            '<form class="form trunced" data-ng-submit="saveMeta()">',
              '<div class="block">',
                '<span>Title</span><input class="lrg bold" data-ng-model="changes.title" data-ng-change="metaChanged()" />',
              '</div>',
              '<div class="block">',
                '<span>Name</span><input class="lrg bold" data-ng-model="currentItem.name" disabled />',
              '</div>',
              '<div class="block" data-ng-repeat="(field, placeholder) in config.options">',
                '<span class="capital">{{field}}</span><input class="lrg bold" data-ng-model="changes[field]" placeholder="{{placeholder}}" data-ng-change="metaChanged()" />',
              '</div>',
              '<button class="btn go submit" data-ng-style="{visibility: $metaChanged ? \'visible\' : \'hidden\'}">Save</button>',
            '</form>',
          '</div>',
          '<h3 class="dark" data-ng-show="pages">',
            '<i class="fa" data-ng-class="{\'fa-angle-down\': !collapsed, \'fa-angle-right\': collapsed}" data-ng-click="collapsed = !collapsed"></i> ',
            'Content',
          '</h3>',
          '<div id="editor" class="panel" data-ng-hide="collapsed || !pages">', 
            '<div class="tabs">',
              '<a data-panel="editor" data-ng-class="{selected: currentPage === page}" data-ng-repeat="page in pages" data-ng-click="select(page)">',
                '{{page | toTitleCase}}',
              '</a>',
            '</div>',
            '<div id="editor-sub">',
              '<div id="editor-version-optns" class="optn-vertical" data-ng-if="currentPage === \'content\'">',
                '<button class="editor-version optn info" data-ng-class="{selected: version.$selected}" data-ng-repeat="version in versions | limitTo:10" data-ng-click="selectVersion(version)">',
                  '<div class="left-arrow"></div>',
                  'Version {{version._index}}',
                '</button>',
              '</div>',
              '<button id="editor-save" class="doc-selector btn go" data-ng-click="save()" data-ng-class="{blank: saving, done: !changed()}" data-ng-disabled="!changed()">',
                '{{ changed() ? \'Save\' : \'Saved\' }}',
                ' <i class="fa fa-circle-o-notch fa-spin" data-ng-if="saving"></i>',
              '</button>',
            '</div>',
            '<div id="editor-content">',
            '</div>',
          '</div>',
          '<div class="loader" data-ng-show="!loaded">',
            '<i class="fa fa-circle-o-notch fa-spin" style="margin-top: 200px;"></i>',
          '</div>'
        ].join(''),
        scope: {
          config: '=mdEditor'
        },
        link: function(scope, element, attrs) {
          var versionModel, resourceBase, mode;

          function createModel(type) {
            resourceBase = MD.root + '/api/' + type;
            return $resource(resourceBase + '/:id/:action', {
              id: '@id'
            }, {
              query: {
                method: 'GET',
                cache: true,
                isArray: true
              },
              update: {
                method: 'PUT'
              }
            });
          }

          scope.pages = scope.config.pages;

          if (scope.pages) {
            scope.currentPage = scope.pages[0];

            editor = ace.edit('editor-content');
            editor.setTheme('ace/theme/eclipse');

            session = editor.getSession();
            session.setTabSize(2);

            session.on('change', function() {
              $timeout(function() {
                scope.$digest();
              });
            });
          }

          function promptSave() {
            scope.prompt = true;
          }

          function resetVersions() {
            scope.versions.forEach(function(version) {
              version.$selected = false;
            });
          }

          function refreshVersions(newModel) {
            if (newModel) {
              scope.currentItem = newModel;
              $cacheFactory.get('$http').remove(resourceBase + '/' + scope.currentItem.id + '/version');
            }
            if (scope.currentPage !== 'content')
              scope.saving = false;
            versionModel.query({ id: scope.currentItem.id, action: 'version' }, function(data) {
              scope.versions = data;
              if (data.length && scope.currentPage === 'content') {
                scope.selectVersion(data[0], scope.saving);
                scope.saving = false;
                scope.loaded = true;
              }
            });
          }

          function changeItem(item) {
            if (scope.changed()) 
              return promptSave();
            if (!item)
              return false;
            if (!versionModel)
              versionModel = createModel(item._type);

            scope.loaded = !scope.pages;

            scope.currentItem = item;
            scope.changes = {
              title: item.title,
              type: item.type
            };

            for(var key in (scope.config.options || {})) {
              scope.changes[key] = item[key];
            }

            Emitter.emit('set.title', item);

            if (scope.pages) {
              refreshVersions();
              session.setValue(scope.currentItem[scope.currentPage]);
              if (scope.currentPage !== 'content')
                scope.loaded = true;

              if (!!~(scope.currentItem.type || '').indexOf('css')) {
                mode = 'css';
              } else {
                mode = scope.config.mode;
              }
              session.setMode('ace/mode/' + mode);
            }
          }

          function getSavedState() {
            if (scope.currentPage === 'content') {
              return scope.versions && scope.versions.length ? scope.versions[0].content : null;
            } else {
              return scope.currentItem[scope.currentPage];
            }
          }

          scope.metaChanged = function() {
            scope.$metaChanged = true;
          };

          scope.select = function(page) {
            scope.currentPage = page;
            session.setValue(scope.currentItem[page]);
          };

          scope.selectVersion = function(version, preserve) {
            scope.currentItem.content = version.content;
            !preserve && session.setValue(scope.currentItem.content);
            resetVersions();
            version.$selected = true;
          }

          scope.changed = function() {
            return scope.pages && scope.currentItem && session.getValue() !== getSavedState();
          };

          scope.save = function() {
            var changes = {
              id: scope.currentItem.id
            };
            if (scope.pages)
              changes[scope.currentPage] = session.getValue();
            
            scope.saving = true;
            versionModel.update(changes, refreshVersions);
          };

          scope.saveMeta = function() {
            versionModel.update({ id: scope.currentItem.id }, scope.changes, function(res) {
              delete scope.$metaChanged;
              $cacheFactory.get('$http').remove(resourceBase + '/' + scope.currentItem.id);
              changeItem(res);
            });
          };

          Emitter.on('navigator.change', changeItem);

          $(window).on('keydown', function(evt) {
            if (String.fromCharCode(event.which).toLowerCase() === 's') {
              if (event.ctrlKey || event.metaKey) {
                event.preventDefault();
                scope.changed() && scope.save();
              }
            }
          });

          window.onbeforeunload = function() {
            if (scope.changed()) {
              return 'Your document has not been saved.';
            }
          };

          scope.$on('$destroy', function() {
            $(window).off('keydown');
          });
        }
      };
    }
  ]);