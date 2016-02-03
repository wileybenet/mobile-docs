<div data-md-navigator="{ type: 'doc', name: 'name', title: 'title' }"></div>
<div class="container white">
  <div class="content docked left with-nav">
    <div data-ng-view></div>
    <div data-md-editor="{ mode: 'html', options: { type: 'text/html', routes: '_ => _' }, pages: ['content'] }"></div>
    <div data-md-asset-manager="{ type: 'doc', name: 'all' }"></div>
  </div>
</div>