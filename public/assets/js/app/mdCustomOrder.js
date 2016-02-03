angular.module('app/mdCustomOrder', ['ngSanitize'])

  .directive('mdCustomOrder', ['$sce', '$http', function($sce, $http) {
    var uid = 0;

    return {
      template: 
        '<form name="form_{{id}}" method="post" data-ng-submit="submit()" data-ng-hide="submitted">'+
          '<table class="form-table full">'+
            '<tbody data-ng-repeat="field in formattedFields">'+
              '<tr data-ng-if="field.$first">'+
                '<td class="heading" colspan="5">{{field.$group}}</td>'+
              '</tr>'+
              '<tr data-ng-show="field.type === \'text\'" data-ng-class="{\'input-group\': field.group}">'+
                '<td class="title" data-ng-class="{first: field.$first, last: field.$last}">'+
                  '<b>{{field.title}}<span data-ng-show="field.required">*</span></b>'+
                '</td>'+
                '<td colspan="3" class="input-field" data-ng-class="{first: field.$first, last: field.$last}">'+
                  '<input class="block" type="text" data-ng-model="form[field.name]" />'+
                '</td>'+
                '<td class="right">'+
                  '&nbsp;{{dollars(extended(field))}}'+
                '</td>'+
              '</tr>'+
              '<tr data-ng-show="field.type === \'textarea\'" data-ng-class="{\'input-group\': field.group}">'+
                '<td class="title" data-ng-class="{first: field.$first, last: field.$last}">'+
                  '<b>{{field.title}}<span data-ng-show="field.required">*</span></b>'+
                '</td>'+
                '<td colspan="3" class="input-field" data-ng-class="{first: field.$first, last: field.$last}">'+
                  '<textarea class="block" type="text" data-ng-model="form[field.name]">{{field.value}}</textarea>'+
                '</td>'+
                '<td></td>'+
              '</tr>'+
              '<tr data-ng-show="field.type === \'select\'" data-ng-class="{\'input-group\': field.group}">'+
                '<td class="title" data-ng-class="{first: field.$first, last: field.$last}">'+
                  '<b>{{field.title}}<span data-ng-show="field.required">*</span></b>'+
                '</td>'+
                '<td class="input-field" data-ng-class="{first: field.$first, last: field.$last}">'+
                  '<select style="width:100px;" class="input-field" data-ng-options="option.id as option.text for option in field.options" data-ng-model="form[field.name]"></select>'+
                '</td>'+
                '<td>'+
                  '{{field.features}}'+
                '</td>'+
                '<td class="subtle right">'+
                  '&nbsp;{{dollars(field.price)}}'+
                '</td>'+
                '<td class="right">'+
                  '&nbsp;{{dollars(extended(field))}}'+
                '</td>'+
              '</tr>'+
              '<tr data-ng-if="field.$last">'+
                '<td colspan="5">&nbsp;</td>'+
              '</tr>'+
            '</tbody>'+
            '<tfoot>'+
              '<tr>'+
                '<td class="title">' +
                  '<span data-ng-show="disabled1()">* Required</span>'+
                '</td>'+
                '<td class="input-field">'+
                  '<button class="btn" type="submit" data-ng-disabled="disabled1() || disabled2()">{{name}}</button>'+
                '</td>'+
                '<td>'+
                  '<span data-ng-show="disabled2()"> Please select at least one Application Certificate.</span>'+
                '</td>'+
                '<td></td>'+
                '<td class="right">{{dollars(total())}}</td>'+
              '</tr>'+
            '</tfoot>'+
          '</table>'+
        '</form>'+
        '<div class="transcluded" data-ng-transclude data-ng-show="submitted"></div>',
      transclude: true,
      scope: {
        name: '=mdCustomOrder',
        fields: '=',
        action: '=',
        redirect: '='
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

        scope.dollars = function(v) {
          if (v == 0) {
            return '';
          }
          var neg = v < 0;
          if (neg) {
            v = -v;
          }
          v = v + '';
          if (v.length > 3) {
            l = v.length;
            v = v.substring(0, l-3) + ',' + v.substring(l-3);
          }
          v = '$' + v + '\xa0US';
          if (neg) {
            v = '(' + v + ')';
          }
          return v;
        }

        scope.extended = function(field) {
          if (field.type === 'select') {
            return field.price*scope.form[field.name];
          } else if (field.price === '-') {
            var price = scope.form[field.name];
            return isNaN(price) ? 0 : -price;
          }
          return 0;
        }

        scope.total = function() {
          var total = 0;
          scope.fields.forEach(function(field) {
            total += scope.extended(field);
          });
          return total;
        }

        scope.disabled1 = function() {
          var incomplete = false;
          scope.fields.filter(function(field) {
            return field.required;
          }).forEach(function(field) {
            if (!scope.form[field.name]) {
              incomplete = true;
            }
          });
          return incomplete;
        };

        scope.disabled2 = function() {
          var atleastone = false;
          scope.fields.filter(function(field) {
            return field.type === 'select';
          }).forEach(function(field) {
            if (field.options.length != 1 && scope.form[field.name] != '0') {
              atleastone = true;
            }
          });
          return !atleastone;
        };

        scope.submit = function() {
          $http.post(MD.root + scope.action, scope.form).success(function(data) {
            location.pathname = MD.root + scope.redirect + data.key;
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