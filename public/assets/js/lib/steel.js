// steel.js is a dependency manager for lightweight (non-framework) web implementations

(function(namespace) {

  var pendingClosureList = [],
    exectutedClosureMap = {};

  function uid() {
    return 'module_' + new Date();
  }

  function foreach(list, cbFn) {
    for (var i = 0; i < list.length; i++) {
      if (list[i]) {
        cbFn(list[i], i);
      }
    }
  }

  function isComplete(dependencyList) {
    var complete = true;
    foreach(dependencyList, function(dep, i) {
      if (dep !== null) {
        complete = false;
      }
    });
    return complete;
  }

  function hasNameCollision(name) {
    if (exectutedClosureMap.hasOwnProperty(name)) {
      throw 'attempted to define same module twice: "' + name + '"';
    }
  }

  function processNewModules(module) {
    var i, closure, processAgain;

    module && pendingClosureList.unshift(module);

    for (i = pendingClosureList.length-1; i >= 0; i--) {
      closure = pendingClosureList[i];
      if (closure.canExecute() && !hasNameCollision(closure.name)) {
        exectutedClosureMap[closure.name] = closure.value;
        pendingClosureList.splice(i, 1);
        processAgain = true;
      }
    }
    if (processAgain) {
      processNewModules();
    }
  }

  // closure class
  function Closure(name, dependencies, module) {
    if (typeof dependencies === 'function') {
      module = dependencies;
      dependencies = [];
    }
    if (typeof name === 'function') {
      module = name;
      dependencies = [];
      name = uid();
    }

    this.name = name;
    this.deps = dependencies;
    this.args = [];
    this.module = module;

    this.value = null;
  }
  Closure.prototype.canExecute = function canExecute() {
    var this_ = this;
    foreach(this.deps, function(name, idx) {
      if (exectutedClosureMap.hasOwnProperty(name)) {
        this_.args[idx] = exectutedClosureMap[name];
        this_.deps[idx] = null;
      }
    });

    if (isComplete(this.deps)) {
      this.value = this.module.apply(namespace, this.args);
      return true;
    }
    return false;
  };

  // module types
  namespace.app = namespace.factory = namespace.service = function(name, deps, module) {
    var module = new Closure(name, deps, module);
    processNewModules(module);
  };

  namespace.component = function(name, deps, module) {
    var module = new Closure(name, deps, module);
    jQuery(window).ready(function() {
      processNewModules(module);
    });
  };

}(window));