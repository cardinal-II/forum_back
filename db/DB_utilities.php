<?php

class DB_utilities
{
  function db_connect()
  {
    if (file_exists('db/database.db')) {
      $pdo = $this->set_db();
    } else {
      $pdo = $this->set_db();
      $this->run_ddl($pdo);
    }
    return $pdo;
  }

  function set_db()
  {
    try {

      $pdo = new PDO('sqlite:db/database.db');

      try {

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_TIMEOUT, 50); //seconds

        $pdo->exec('PRAGMA journal_mode = WAL; PRAGMA synchronous = EXTRA;');
        $pdo->exec('PRAGMA busy_timeout = 50000;');
        //echo 'Connection is ok.' . "<br>";
        return $pdo;

      } catch (PDOException $e) {
        echo 'Connection failed 2: ' . $e->getmessage(), "<br>";
        throw new PDOException($e->getMessage(), (int) $e->getCode());
      }

    } catch (PDOException $e) {
      echo 'Connection failed 1: ' . $e->getmessage(), "<br>";
      throw new PDOException($e->getMessage(), (int) $e->getCode());
    }
  }

  function run_ddl($pdo)
  {
    try {
      
      //echo 'run_ddl 20' . PHP_EOL;
      $ddl_10_first = file_get_contents('db/ddl_10_first.sql');
      //$this->db_interface($pdo, $ddl_10_first, [], 'ddl_10_first', 0, true);
      //echo 'run_ddl 30' . PHP_EOL;
      $ddl_30_tables_reference = file_get_contents('db/ddl_30_tables_reference.sql');
      //$this->db_interface($pdo, $ddl_30_tables_reference, [], 'ddl_30_tables_reference', 0, true);
      //echo 'run_ddl 40' . PHP_EOL;
      $ddl_40_tables_reference_data = file_get_contents('db/ddl_40_tables_reference_data.sql');
      //$this->db_interface($pdo, $ddl_40_tables_reference_data, [], 'ddl_40_tables_reference_data', 0, true);
      //echo 'run_ddl 50' . PHP_EOL;
      $ddl_70_getters = file_get_contents('db/ddl_70_getters.sql');
      //$this->db_interface($pdo, $ddl_70_getters, [], 'ddl_70_getters', 0, true);

      //Execute all script files at once within one transaction:
      $ddl_all = $ddl_10_first . $ddl_30_tables_reference . $ddl_40_tables_reference_data . $ddl_70_getters;
      $this->db_interface($pdo, $ddl_all, [], 'ddl_all', 0, true);
    } catch (\Throwable $th) {
      //echo 'run_ddl 200' . $th . PHP_EOL;
    }

    /*
        $ddl_tables_reference_transaction = file_get_contents('ddl_tables_transaction.sql');
        $this->db_interface($pdo, $ddl_tables_reference_transaction, [], 'ddl_tables_reference_transaction', 0);

        $ddl_tables_reference_transaction_data = file_get_contents('ddl_tables_transaction_data.sql');
        $this->db_interface($pdo, $ddl_tables_reference_transaction_data, [], 'ddl_tables_reference_transaction_data', 0);

        $ddl_triggers = file_get_contents('ddl_triggers.sql');
        $this->db_interface($pdo, $ddl_triggers, [], 'ddl_triggers', 0);

        $ddl_views = file_get_contents('ddl_views.sql');
        $this->db_interface($pdo, $ddl_views, [], 'ddl_views', 0);

        $ddl_last = file_get_contents('ddl_last.sql');
        $this->db_interface($pdo, $ddl_last, [], 'ddl_last', 0);
    */
  }

  function db_interface($pdo, $text, $values, $caller, $result_set_type, $init = false)
  {
    try {
      //echo 'db_interface, 100: ' . $transaction_count . PHP_EOL;
      // if ($transaction_count == 0) {
      //   //echo 'db_interface, 200' . PHP_EOL;
      //   $pdo->beginTransaction();
      //   //echo 'db_interface, 300' . PHP_EOL;
      // }

      if(!$pdo->inTransaction()){$pdo->beginTransaction();}
      //echo 'db_interface, 400' . PHP_EOL;
      //insert, update, select, delete
      if ($init) {
        $pdo->exec($text);
        // if ($transaction_count == 0) {
        //   //echo '200', $affected;
        //   $pdo->commit();
        //   //echo '300';
        // }

        if($pdo->inTransaction()){$pdo->commit();}
        return;
      }

      $stmt = $pdo->prepare($text);
      //echo 'db_interface, 500' . PHP_EOL;
      $stmt->execute($values);
      //echo 'db_interface, 600' . PHP_EOL;
      $affected = $stmt->rowCount();
      //echo 'db_interface, 700' . PHP_EOL;
      $result = $stmt->fetchAll();

      //echo 'db_interface, 800' . PHP_EOL;
      // if ($transaction_count == 0) {
      //   //echo '200', $affected;
      //   $pdo->commit();
      //   //echo '300';
      // }

      if($pdo->inTransaction()){$pdo->commit();}

      if ($result_set_type == 0) { //case of insert, update, delete: the db isn't returning result.

        if ($affected !== 0) {
          return $affected;
        } else {
          echo 'db_interface, 900: no rows affected.';
        }

      } else {
        //$result = $stmt->fetchAll();
        //if ($affected == 0) {return null;}
        if ($result_set_type == 1) { //возвращает скаляр: null records or one record.
          //echo $result[0];
          return $result[0]["$caller"];
        } else { //возвращает записи (состояющие из одного или более полей).
          //echo 'interface 2, ', $result;
          return $result;
        }
      }
    } catch (PDOException $e) {
      //echo 'exception: ' . $e;
      if ($e->getCode() == 'SQLITE_BUSY' || $e->getCode() == 'SQLITE_LOCKED') { // || $e->getCode() == 'SQLITE_ABORT' || $e->getCode() == 'SQLITE_CONSTRAINT' || $e->getCode() == 'SQLITE_CONSTRAINT_CHECK') {
        return $this->db_interface($pdo, $text, $values, $caller, $result_set_type, $init);
      } else {
        //echo 'rollback: ' . $e;
        echo 'db_interface, catch, 1100' . $e . PHP_EOL;
        $pdo->rollback();
        throw new PDOException($e->getMessage(), (int) $e->getCode());
      }
    }
  }

}