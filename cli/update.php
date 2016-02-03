<?php // php cli/update.php [path to -lit]


  require_once('../../config/config.inc.php');

  $path = array_slice($argv, 1)[0];

  $folders = ['announcements', 'solutions'];
  foreach ($folders as $group) {

    $mapping = [
      'announcements' => ['Post', 'post'],
      'solutions' => ['Solution', 'solution']
    ];


    Database::$mysql->query("TRUNCATE {$mapping[$group][1]};");
    Database::$mysql->query("TRUNCATE {$mapping[$group][1]}_content;");

    $dir = "$path/$group/";

    if (is_dir($dir)) {
      foreach (scandir($dir) as $filename) {
        if ($filename[0] != '.' && is_dir($sub_dir = $dir . "/" . $filename)) {
          $name = "";
          $date = "";
          $summary = "";
          $content = "";
          foreach (scandir($sub_dir) as $sub_name) {
            $file = fopen($sub_path = $sub_dir . "/" . $sub_name, "r") or die("Unable to open file!");
            $contents = fread($file, filesize($sub_path));
            if ($sub_name == 'content.html') {
              $content = $contents;
            } elseif ($sub_name == 'summary.html') {
              $summary = $contents;
            } elseif ($sub_name == $sub_dir . '.html') {
              preg_replace_callback('/<td>([0-9]{4}-[0-9]{2}-[0-9]{2})<\/td>/', function($matches) {
                global $date;
                $date = $matches[1];
              }, $contents);
              preg_replace_callback('/title[^>]*>[^>]*>([^<]+)/', function($matches) {
                global $name;
                $name = $matches[1];
              }, $contents);

            }
            fclose($file);
          }
          $model = $mapping[$group][0]::create(['title' => $name, 'name' => $filename, 'date' => $date, 'summary' => $summary]);
          $c = $mapping[$group][0] . 'Content';
          $c::create([($mapping[$group][1] . '_id') => $model['id'], 'content' => $content]);
          echo $group . ': ' . $model['id'] . "\n";
        }
      }
    }
  }

?>