<?php

class WebPage
{
  private $ut;
  private $db;
  private $pdo;

  function beforeRoute()
  {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST))
      $_POST = json_decode(file_get_contents('php://input'), true);

    // if ($_SERVER['REQUEST_METHOD'] == 'GET' && empty($_GET))
    //   $_GET = json_decode(file_get_contents('php://input'), true);

    $this->ut = new Utilities();
    $this->db = new DB_utilities();
    $this->pdo = $this->db->db_connect();

  }

  function about()
  {
    $data = array('id' => 778, 'response' => 'It is About view.');
    echo $this->ut->respond_json($data);
  }

  function root()
  {
    $data = array('id' => 779, 'response' => 'Hello, world! It is the root route.');
    echo $this->ut->respond_json($data);
  }

  function get_user_info($f3, $params)
  {
    //generate uuid as a fake user name
    $uuid = $this->ut->random_string(64);
    $sql = 'insert into users(uuid, sex) values(?, ?)';
    $result = $this->db->db_interface($this->pdo, $sql, [$uuid, $f3->get('GET.sex')], 'users', 0);
    //echo 'get_user_info 200' . PHP_EOL;
    if ($result === false) {
      echo 'users: The SQL query failed with error ';// . $this->pdo->errorCode;
      return;
    }

    //get the user_id
    $sql = 'select user_id from users where uuid = ?';
    $user_id = $this->db->db_interface($this->pdo, $sql, [$uuid], 'user_id', 1);
    if ($user_id === false) {
      echo 'users: The SQL query failed with error ';// . $this->pdo->errorCode;
      return;
    }

    //calc a team_id to assign the user:
    $sql = 'select team_id from next_team where user_id = ?';
    $result = $this->db->db_interface($this->pdo, $sql, [$user_id], 'team_id', 1);
    if ($result === false) {
      echo 'next_team: The SQL query failed with error ';// . $this->pdo->errorCode;
      return;
    }
    // echo 'team_id: ';
    // var_dump($result);
    // print_r($result);

    $team_id = $result;

    //assign the user to a team
    $sql = 'update users set team_id = ? where user_id = ?';
    $result = $this->db->db_interface($this->pdo, $sql, [$team_id, $user_id], 'users', 0);
    if ($result === false) {
      echo 'users: The SQL query failed with error ';// . $this->pdo->errorCode;
      return;
    }

    //echo 'assign a team', $result . PHP_EOL;

    //get a map of the team
    $sql = 'select location_id, station_id, direction, image_path from teams where team_id = ?';
    $result = $this->db->db_interface($this->pdo, $sql, [$team_id], 'teams', 2);

    if ($result === false) {
      echo 'teams: The SQL query failed with error ';// . $this->pdo->errorCode;
      return;
    }

    //add user_id, team_id to the response
    $response = $result[0];
    $response['team_id'] = $team_id;
    $response['user_id'] = $user_id;

    echo $this->ut->respond_json($response);

  }

  function get_photo($f3){
    $sql = 'select image_path from teams t left join users u on t.team_id = u.team_id where user_id = ?';
    $image_path = $this->db->db_interface($this->pdo, $sql, [$f3->get('GET.user_id')], 'image_path', 1);
    if ($image_path === false) {
      echo 'teams users: The SQL query failed with error ';// . $this->pdo->errorCode;
      return;
    }
    // echo 'image_path: ', $image_path . PHP_EOL;
    // echo "DOCUMENT_ROOT: " . $_SERVER["DOCUMENT_ROOT"] . PHP_EOL;
    // echo "__DIR__: " . __DIR__ . PHP_EOL;
    // echo "getcwd: " . getcwd() . PHP_EOL;
    //$fileContent = file_get_contents(getcwd() . '/' . $image_path); // '/images/image_1_backward.png'); //$image_path);
    $fileContent = file_get_contents($image_path);
    header('Content-Type: image/png');

    // Send the file content to the client
    echo $fileContent;
    die();
  }

  function get_stat()
  {
    $sql = 'select users_count, male_share, assigned_teams, min_users_in_teams, diff from stat_all;';
    $result = $this->db->db_interface($this->pdo, $sql, [], 'stat_all', 2);
    if ($result == false) {
      $result = [[]];

    }


    $response = $result[0];
    //$response['diff'] = $diff;
    
    echo $this->ut->respond_json($response);

  }

  function get_teams_stat()
  {
    $sql = 'select team_id, cnt from teams_cnt_users;';
    $result = $this->db->db_interface($this->pdo, $sql, [], 'teams_cnt_users', 2);
    if ($result == false) {
      $result = [[]];
    }
    
    echo $this->ut->respond_json($result);
  }

  function brew_count($f3)
  {
    $data = array('id' => 775, 'response' => $f3->get('PARAMS.count') . ' bottles of beer on the wall.');
    echo $this->ut->respond_json($data);
  }

  function brew()
  {
    $data = array('id' => 776, 'response' => 'It is brew view.');
    echo $this->ut->respond_json($data);
  }

  function newpage()
  {
    $data = array('id' => 777, 'response' => 'It is newpage view.');
    echo $this->ut->respond_json($data);
  }

  function delete_table1($f3)
  {
    $sql = 'delete from table1';
    $result = $this->db->db_interface($this->pdo, $sql, [], 'table1', 0);
    //echo 'result: ', $result;
    echo $this->ut->respond_json($result);

  }

  function table1()
  {
    $sql = 'select * from table1';
    $result = $this->db->db_interface($this->pdo, $sql, [], 'table1', 2);
    //echo 'result: ', $result;

    if ($result !== false) {
      echo $this->ut->respond_json($result);
      //print_r($result);
    } else {
      echo 'table1: The SQL query failed with error ';// . $this->pdo->errorCode;
    }

  }

  function table1_field2()
  {
    $sql = 'select field2 from table1 where field1 = 46';
    $result = $this->db->db_interface($this->pdo, $sql, [], 'field2', 1);
    //echo 'result: ', $result;
    echo $this->ut->respond_json($result);

  }

  function add_table1($f3)
  {
    // print_r ($f3->get('POST')) . PHP_EOL;
    //echo $f3->get('POST.field1') . PHP_EOL;
    // echo $f3->get('POST.field2') . PHP_EOL;
    //echo $f3->get('PARAM.field1') . PHP_EOL;
    //print_r($f3->BODY);

    //echo $f3->BODY['field1'];
    //print_r(json_decode($f3->get('POST'), true));
    //print_r ($f3->get('POST')) . PHP_EOL;
    //print_r ($_POST) . PHP_EOL;
    //echo $_POST['field1'] . PHP_EOL;
    // echo $_POST['field2'] . PHP_EOL;
    //echo $this->ut->respond_json($f3->get('POST'));

    // $sql = 'insert into table1(field1, field2) values(?, ?)';
    // $result = $this->db->db_interface($this->pdo, $sql, [$f3->get('POST.field1'), $f3->get('POST.field2')], 'table1', 0);

    $sql = 'insert into table1(field1, field2) values(?, ?) returning field1, field2';
    $result = $this->db->db_interface($this->pdo, $sql, [$f3->get('POST.field1'), $f3->get('POST.field2')], 'table1', 2);

    if ($result !== false) {
      echo $this->ut->respond_json($result);
      //print_r($result);
    } else {
      echo 'table1: The SQL query failed with error ' . $this->pdo->errorCode;
    }




    //echo 'result: ', $result;
    //echo $this->ut->respond_json($result);

  }

  function afterRoute()
  {
    //echo 'After route function.', "<br>";
  }

}