<?php

  // POPUP example: http://localhost:8888/basic/#!/WWB-doc_language___def.htm 
  //  click on "project"

  // hashbang rewrite: basic/?_escaped_fragment_=WWB-doc_language___def.htm
  
  class ReferenceCtrl extends Ctrl {

    public function get_nav($params = []) {
      header('Cache-Control: max-age=288000');
      Render::json(Reference::navigation());
    }

    public function get_file($params = []) {
      $link_style = isset($params['link_style']) ? $params['link_style'] : 0;
      $reference = Reference::read_file($params['file_name'], $link_style);
      SiteStructure::set_page_title($reference['title']);
      Render::html($reference['content'], [], 'text/html');
    }

    public function get_ref($params = []) {
      //echo '<pre>'; print_r($params); echo '</pre>'; exit;
      if (isset($params['_escaped_fragment_'])) {
        $file_name = substr($params['_escaped_fragment_'], 5);
        $context = [
          'page' => $file_name,
          'link_style' => -1 // create /#!/ links
        ];
        $content = Template::render_doc_by_name('basic-ref', $context);
        Render::html($content);
      } else {
        Render::include_script("app/mdCode");
        parent::doc($params);
      }
    }

  }

?>