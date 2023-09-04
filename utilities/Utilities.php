<?php

class Utilities {
    function respond_json($data) {
      header('Content-Type: application/json');
    	return json_encode($data);
    }

    function random_string($length) {
      $str = random_bytes($length);
      $str = base64_encode($str);
      $str = str_replace(["+", "/", "="], "", $str);
      $str = substr($str, 0, $length);
      return $str;
  }
  

  }
