<?php
namespace App\Core;

require_once BASE_PATH . '/db/database.php';

use Database;
use PDO;

abstract class Store
{
  protected PDO $db;

  public function __construct()
  {
    $this->db = Database::getInstance()->getConnection();
  }
}