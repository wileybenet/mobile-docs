angular.module('app/mdMenuEditor', ['ngResource'])

  .run(['$templateCache', function ($templateCache) {
    $templateCache.put('menu-editor-child',
      '<div data-drop="doc.moveBefore(item)" class="order-tray" data-ng-class="{\'item-dragging\': $root.$dragging && !checkParent($root.$dragging, doc)}" data-ng-hide="!$root.$dragging || checkParent($root.$dragging, doc)">'+
        '<i class="fa fa-long-arrow-right"></i>'+
      '</div>'+
      '<div class="title">'+
        '<span class="title-text"><i class="fa fa-arrows" data-ng-style="{\'visibility\': $root.$dragging ? \'hidden\' : \'visible\'}"></i> {{doc.title}}</span>'+
        '<span data-drop="doc.addChild(item)" class="child-tray" title="Add as child of {{doc.title}}" data-ng-class="{\'item-dragging\': $root.$dragging && !checkParent($root.$dragging, doc)}" data-ng-hide="!$root.$dragging || checkParent($root.$dragging, doc) || children(doc).length">'+
          '<i class="fa fa-level-down"></i>'+
        '</span>'+
        // '<span class="sub-action" data-stop-prop>'+
        //   '<i class="fa fa-globe" data-ng-class="{selected: doc.published}" data-ng-click="doc.published = !doc.published"></i>'+
        // '</span>'+
      '</div>'+
      '<div data-drop="doc.moveAfter(item)" class="order-tray" data-ng-class="{\'item-dragging\': $root.$dragging && !checkParent($root.$dragging, doc)}" data-ng-hide="!$root.$dragging || checkParent($root.$dragging, doc) || children(doc).length || !$last">'+
        '<i class="fa fa-long-arrow-right"></i>'+
      '</div>'+
      '<div class="child-container">'+
        '<div data-drag="doc" data-ng-repeat="doc in children(doc)" data-ng-include="\'menu-editor-child\'"></div>'+
      '</div>');
  }])

  .directive('mdMenuEditor', ['$resource', '$q', '$timeout', function($resource, $q, $timeout) {
    return {
      template: 
        '<div class="active">'+
          '<div class="well">'+
            '<div data-drag="doc" data-ng-repeat="doc in children()" class="item" data-ng-include="\'menu-editor-child\'">'+
              '<i class="fa fa-long-arrow-right"></i>'+
            '</div>'+
          '</div>'+
        '</div>'+
        '<div class="inactive">'+
          '<h3 class="center">Inactive</h3>'+
          '<div class="well transparent">'+
            '<div data-drag="doc" data-ng-repeat="doc in inactive()" data-ng-include="\'menu-editor-child\'"></div>'+
            '<div data-drop="inactiveReceive(item)" class="inactive-tray" data-ng-class="{\'item-dragging\': $root.$dragging && $root.$dragging.id !== doc.id}" data-ng-style="{visibility: $root.$dragging ? \'visible\' : \'hidden\'}">'+
              'Inactive'+
            '</div>'+
          '</div>'+
        '</div>'+
        '<div class="menu-actions clear">'+
          '<button class="btn go blank left" data-ng-click="save()" data-ng-disabled="!changed()">{{changed() ? \'Save\' : \'Saved\'}}</button>'+
          '<button class="btn blank right" data-ng-click="reset()">Reset</button>'+
          '<div class="clear"></div>'+
        '</div>',
      link: function(scope, element, attrs) {
        var model = $resource(MD.root + '/api/doc/:id/:action', {
            id: '@id'
          }, {
            update: {
              method: 'PUT'
            }
          }),
          docList = [],
          parentChanges = {};

        scope.checkParent = function(current, dropzone) {
          if (+current.id === +dropzone.id)
            return true;
          if (+dropzone.parent_id) {
            var parent = scope.active().findWhere({id: dropzone.parent_id});
            if (parent.id === current.id)
              return true;
            else
              return scope.checkParent(current, parent);
          } else {
            return false;
          }
        }

        scope.active = function() {
          return docList.filter(function(doc) {
            return doc.parent_id !== null;
          });
        };
        scope.inactive = function() {
          return docList.filter(function(doc) {
            return doc.parent_id === null;
          });
        };

        scope.reset = function() {
          model.query(function(data) {
            docList = data;
            data.forEach(function(doc) {
              doc.addChild = function(child) {
                scope.$apply(function() {
                  child.parent_id = doc.id;
                  parentChanges[child.id] = child.parent_id;
                });
              };
              doc.moveBefore = function(child) {
                scope.$apply(function() {
                  var removeIdx = docList.findIndex(function(doc) {
                    return doc.id === child.id;
                  });
                  if (removeIdx > -1) {
                    docList.splice(removeIdx, 1);
                  }
                  var insertIdx = docList.findIndex(function(item) {
                    return item.id === doc.id;
                  });
                  docList.splice(insertIdx, 0, child);

                  child.parent_id = doc.parent_id;
                  parentChanges[child.id] = child.parent_id;
                });
              };
              doc.moveAfter = function(child) {
                scope.$apply(function() {
                  var removeIdx = docList.findIndex(function(doc) {
                    return doc.id === child.id;
                  });
                  if (removeIdx > -1) {
                    docList.splice(removeIdx, 1);
                  }
                  var insertIdx = docList.findIndex(function(item) {
                    return item.id === doc.id;
                  });
                  docList.splice(insertIdx + 1, 0, child);
                  
                  child.parent_id = doc.parent_id;
                  parentChanges[child.id] = child.parent_id;
                });
              };
            });
          });
        }
        scope.reset();

        scope.inactiveReceive = function(item) {
          $timeout(function() {
            item.parent_id = null;
            parentChanges[item.id] = item.parent_id;
          });
        };

        scope.children = function(doc) {
          return scope.active().filter(function(item) {
            return doc ? (+item.parent_id === +doc.id) : !+item.parent_id;
          });
        };

        scope.changed = function() {
          for (var key in parentChanges) {
            return true;
          }
          return false;
        };

        scope.save = function() {
          var parents = docList.groupBy('parent_id'),
            orderChanges = {};
          for (var id in parents) {
            parents[id].forEach(function(doc, idx) {
              orderChanges[doc.id] = idx;
            });
          }

          model.update({ action: 'nav' }, { parent: parentChanges, order: orderChanges }, function() {
            parentChanges = {};
            orderChanges = {};
          });
        };

        element.attr({
          id: 'menu-editor'
        });
      }
    };
  }]);