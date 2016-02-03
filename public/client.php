<?php // Route resolver

  // Load configs
  require_once('../../config/config.inc.php');

  ContentProcessor::pre();

  Session::start();

  SiteStructure::initialize();

  Router::initialize();
  Render::initialize();


  // Resolve the incoming request
  $parsed_request = Router::resolve($_SERVER['REQUEST_METHOD'], SiteStructure::get_request_uri());

  if ($parsed_request) {
    Ctrl::process($parsed_request);
  } else {
    Render::error_404();
  }

?>