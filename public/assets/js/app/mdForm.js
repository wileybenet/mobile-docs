angular.module('app/mdForm', ['ngSanitize'])

  .directive('mdForm', ['$sce', '$http', function($sce, $http) {
    var uid = 0;

    return {
      template: 
        '<form name="form_{{id}}" method="post" data-ng-submit="submit()" data-ng-hide="submitted">'+
          '<table class="form-table full">'+
            '<tbody data-ng-repeat="field in formattedFields">'+
              '<tr data-ng-if="field.$first">'+
                '<td class="heading" colspan="2">{{field.$group}}</td>'+
              '</tr>'+
              '<tr data-ng-class="{\'input-group\': field.group}">'+
                '<td class="title" data-ng-class="{first: field.$first, last: field.$last}">'+
                  '<b>{{field.title}}<span data-ng-show="field.required">*</span></b>'+
                '</td>'+
                '<td class="input-field" data-ng-class="{first: field.$first, last: field.$last}">'+
                  '<input data-ng-show="field.type === \'text\'" class="block" type="text" data-ng-model="form[field.name]" />'+
                  '<textarea data-ng-show="field.type === \'textarea\'" class="block" type="text" data-ng-model="form[field.name]">{{field.value}}</textarea>'+
                  '<div data-ng-repeat="option in field.options" data-ng-show="field.type === \'checkbox\'">'+
                    '<label>'+
                      '<input data-ng-show="field.type === \'checkbox\'" type="checkbox" data-ng-model="form[field.name][option.id]" />'+
                      '<div class="input-text" data-ng-bind-html="option.text"></div>'+
                    '</label>'+
                  '</div>'+
                  '<select data-ng-show="field.type === \'select\'" class="input-field" data-ng-options="option.id as option.text for option in field.options" data-ng-model="form[field.name]"></select>'+
                  '<a data-ng-show="field.html && field.html.tag === \'a\'" data-ng-href="{{root}}/{{field.html.attr.href}}" target="field.target">'+
                    '{{field.html.text}}'+
                  '</a>'+
                  '<div data-ng-show="field.html && !field.html.tag" data-ng-bind-html="field.html"></div>'+
                '</td>'+
              '</tr>'+
              '<tr data-ng-if="field.$last">'+
                '<td colspan="2">&nbsp;</td>'+
              '</tr>'+
            '</tbody>'+
            '<tfoot>'+
              '<tr>'+
                '<td class="title">'+
                  '<span data-ng-show="disabled()">* Required</span>'+
                '</td>'+
                '<td class="input-field">'+
                  '<button class="btn" type="submit" data-ng-disabled="disabled()">{{name}}</button>'+
                  '<span data-ng-show="disabled()"> Please finish filling out the form.</span>'+
                '</td>'+
              '</tr>'+
            '</tfoot>'+
          '</table>'+
        '</form>'+
        '<div class="transcluded" data-ng-transclude data-ng-show="submitted"></div>',
      transclude: true,
      scope: {
        name: '=mdForm',
        fields: '=',
        action: '='
      },
      link: function(scope, element, attrs) {
        var groups;

        function process(text) {
          return text.replace(/\[(\w)(.*?)\](.*?)\[\/\1\]/g, function(match, tag, attrs, content) {
            return '<' + tag + attrs + '>' + content + '</' + tag + '>';
          });
        }

        scope.root = MD.root;

        scope.id = uid++;

        scope.form = {};

        scope.fields.forEach(function(field) {
          if (field.value) {
            scope.form[field.name] = field.value;
          }
          if (field.options) {
            scope.form[field.name] = {};
            field.options.forEach(function(option) {
              option.text = $sce.trustAsHtml(process(option.text));
            });
          }
          if (field.type === 'select') {
            scope.form[field.name] = field.options[0].id;
          }
          if (field.html && typeof field.html === 'string') {
            var html = element.find(field.html).html();
            field.html = $sce.trustAsHtml(html);
          }
        });

        groups = scope.fields.filter(function(field) { return field.group; });
        if (groups.length) {
          groups = groups.groupBy('group');
          for (var group in groups) {
            groups[group][0].$group = groups[group][0].group;
            groups[group][0].$first = true;
            groups[group].last().$last = true;
            groups[group].slice(1).forEach(function(field) {
              field.$hide = true;
            });
          }
        }

        scope.formattedFields = scope.fields;

        scope.disabled = function() {
          var incomplete = false;
          scope.fields.filter(function(field) {
            return field.required;
          }).forEach(function(field) {
            if (field.type === 'checkbox') {
              var atLeastOne = false;
              for (var key in scope.form[field.name]) {
                if (scope.form[field.name][key]) {
                  atLeastOne = true;
                }
              }
              if (!atLeastOne) {
                incomplete = true;
              }
            } else if (!scope.form[field.name]) {
              incomplete = true;
            }
          });
          return incomplete;
        };

        scope.submit = function() {
          $http.post(MD.root + scope.action, scope.form).success(function(data) {
            element.find('#form-success').show();
            scope.submitted = true;
          }, function() {
            element.find('#form-failure').show();
            scope.submitted = true;
          });
        };

        element.addClass('md-form');
      }
    };
  }]);