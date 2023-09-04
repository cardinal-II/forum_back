<?php

class Item {
    function beforeRoute() {
        return new Utilities();
      }

    function get($f3) {
        $data = array('id'=>555, 'response'=>$f3->get('PARAMS.item').' is the cart id.');
        echo $this->beforeRoute()->respond_json($data);
    }
    
    function post() {}
    function put() {}
    function delete() {}
}

