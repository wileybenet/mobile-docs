<?php

  class Record {

    private static function table_name($class_name) {
      return strtolower(preg_replace_callback('/([a-z])([A-Z])/', function($matches) {
        return $matches[1] . '_' . strtolower($matches[2]);
      }, $class_name));
    }

    public static function read($fields = ['*'], $id = FALSE, $where = FALSE, $order = '') {
      $table = self::table_name(get_called_class());
      $fields = implode(', ', $fields);
      if ($id && (is_numeric($id) || $id === TRUE)) {
        if (is_numeric($id)) {
          $id = Database::sanitize($id);
          $id = "`$table`.`id` = $id";
        } else {
          $id = '';
        }
        $where = $where ? Database::sanitize($where) : $where;
        $res = Database::query("SELECT $fields FROM $table WHERE $id $where LIMIT 1;");
        if ($errors = Database::$mysql->error) {
          return ['error' => $errors];
        } else if ($row = $res->fetch_assoc()) {
          $row['_class'] = $table;
          $row['_index'] = 0;
          return $row;
        } else {
          return ['error' => '500'];
        }
      } else {
        if ($where) {
          $where = Database::sanitize($where);
          $where = 'WHERE ' . $where;
        }
        if ($order) {
          $order = 'ORDER BY ' . $order;
        }

        $res = Database::query("SELECT $fields FROM $table $where $order;");
        if ($errors = Database::$mysql->error) {
          return $errors;
        } else {
          return self::post_process($res, $table);
        }
      }
    }

    public static function post_process($res, $table = FALSE) {
      $table = $table ? $table : self::table_name(get_called_class());

      $items = [];
      $index = $res->num_rows;
      while ($row = $res->fetch_assoc()) {
        $row['_class'] = $table;
        $row['_index'] = $index--;
        $items[] = $row;
      }
      return $items;
    }

    public static function update($model, $id, $update_lit = '') {
      $table = self::table_name(get_called_class());
      if (count($model) < 1) {
        return FALSE;
      }
      $id = Database::sanitize($id);
      $update_array = [];
      foreach ($model as $key => $value) {
        $key = Database::sanitize($key, TRUE);
        $value = Database::sanitize($value);
        $update_array[] = "`$key` = $value";
      }
      $update_str = implode(', ', $update_array);
      if ($update_lit != '') {
        $update_str .= ', ' . $update_lit;
      }
      $res = Database::query("UPDATE $table SET $update_str WHERE id = $id LIMIT 1;");
      if ($errors = Database::$mysql->error) {
        return ['error' => $errors];
      } else if ($res) {
        return ['success' => $res];
      } else {
        return ['error' => '500'];
      }
      return !$errors;
    }

    public static function create($model) {
      $table = self::table_name(get_called_class());
      $create_keys = [];
      $create_values = [];
      foreach ($model as $key => $value) {
        $create_keys[] = Database::sanitize($key, TRUE);
        $create_values[] = Database::sanitize($value);
      }
      $create_keys = implode(',', $create_keys);
      $create_values = implode(',', $create_values);

      $res = Database::query("INSERT INTO $table ($create_keys) VALUES ($create_values);");
      if ($errors = Database::$mysql->error) {
        return ['error' => $errors];
      } else if ($res) {
        $model['_class'] = $table;
        $model['id'] = Database::$mysql->insert_id;
        return $model;
      } else {
        return ['error' => '500'];
      }
    }
    
    public static function unique_key($field, $size, $value) {
      $table = self::table_name(get_called_class());
      $t = time();
      $t -= $t % 86400;
      $t /= 86400;
      $value = $t . "surprizing-hidden-key" . $value;
      $i = 0;
      while (true) {
        $key = substr(md5($i . $value), 0, $size);
        if (str_replace("0", "", $key) != "" && str_replace("f", "", $key) != "") {
          $key = substr(md5($i . $value), 0, $size);
          $res = Database::query("SELECT COUNT(*) FROM $table WHERE $field='$key' GROUP BY '$key';");
          if ($errors = Database::$mysql->error) {
            return ['error' => $errors];
          }
          $row = $res->fetch_assoc();
          if ($row['COUNT(*)'] == 0) {
            return $key;
          }
          ++$i;
        }
      }
    }

    public static function truncate() {
      $table = self::table_name(get_called_class());
      $res = Database::query("TRUNCATE $table;");
      if ($errors = Database::$mysql->error) {
        return ['error' => $errors];
      }
    }

    // PUT/POST parameter filtering
    public static function allow($model, $allowed_keys) {
      $allowed = [];
      foreach ($model as $key => $value) {
        if (in_array($key, $allowed_keys)) {
          $allowed[$key] = $value;
        }
      }
      return $allowed;
    }
  }

?>