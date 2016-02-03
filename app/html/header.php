<!DOCTYPE html>
<html>
  <head>
    <title>WinWrap&reg; | {{{page_title}}}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="/assets/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/reference.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/layout.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/main.css">
    <script type="text/javascript">
      (function() {
        window.MD = {
          root: '{{root}}'
        };
      }());
    </script>
    <script type="text/javascript" src="/assets/js/lib/es5-shim.js"></script>
    <script type="text/javascript" src="/assets/js/lib/jquery.js"></script>
    <--get_scripts-->
    <script type="text/javascript">
      (function() {
        if (window.hasOwnProperty('angular')) {
          angular.module('winwrap', [
            'filters', 'directives', 'factories', 'services',
            <--get_script_names-->
          ]);
        }
        $(document).on('ready', function() {
          $('#site-search input')
            .on('focus', function() {
              $(this).stop(true, true).animate({ width: '100px' }, 150);
            })
            .on('blur', function() {
              $(this).stop(true, true).animate({ width: '40px' }, 50);
            });
        });
      }());
    </script>
    <script type="text/javascript">
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

      ga('create', 'UA-56235497-1', 'auto');
      ga('send', 'pageview');
      if (location.href != '' && location.href != 'http:') {
        ga('send', 'event', 'pageview', location.href);
      }
    </script>
  </head>
  <body data-ng-app="winwrap">
    {{#admin}}
    <div id="admin-nav">
      <h4>Admin</h4>
      {{#admin_nav}}
      <a class="btn {{#selected}} selected {{/selected}}" href="/md/{{name}}">{{title}}</a>
      {{/admin_nav}}
      <div class="admin-footer">
        <a href="/md/evaluators" target="_blank">Evaluators <i class="fa fa-cloud-download"></i></a>
        <a href="/md/bannerhits" target="_blank">Banner Hits <i class="fa fa-flag"></i></a>
        <a href="/md/adhits" target="_blank">Ad Hits <i class="fa fa-hand-o-right"></i></a>
        <a href="/md/quotes" target="_blank">Quotes <i class="fa fa-quote-right"></i></a>
        <a href="" data-md-modal="'/md/interface/html'">HTML Guide <i class="fa fa-code"></i></a>
        <a href="/md/style-guide" target="_blank">Style Guide <i class="fa fa-paint-brush"></i></a>
        <a href="/md/routes" target="_blank">App Routes <i class="fa fa-code-fork fa-rotate-90"></i></a>
        <a href="/md/logout">Logout <i class="fa fa-sign-out"></i></a>
      </div>
    </div>
    {{/admin}}
    <div id="heading" class="{{#admin}} nav-shift {{/admin}}">
      <div class="content left">
        <a id="title-wrapper" href="/">
          
        </a>
        <div id="top-nav">
          {{#main_nav}}
          <a href="/{{name}}/" {{#selected}} class="selected" {{/selected}}>{{title}}</a>
          {{/main_nav}}
          <form id="site-search" method="GET" action="/search">
            <i class="fa fa-search magnifiying-glass"></i>
            <input name="query" placeholder="search" type="text" />
          </form>
        </div>
      </div>
    </div>
    <div id="content-wrapper" {{#admin}} class="admin-shift" {{/admin}}>
      <div id="heading-spacer"></div>