<?php

require_once BASE_PATH . '/config/config.php';

class Database
{
  private static ?Database $instance = null;
  private PDO $connection;

  private function __construct()
  {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

    $options = [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
      PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
    ];

    try {
      $this->connection = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
    } catch (PDOException $e) {
      die("Lỗi kết nối DB: " . $e->getMessage());
    }
  }

  public static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new Database();
    }
    return self::$instance;
  }

  public function getConnection()
  {
    return $this->connection;
  }

  // TRANSACTION
  public function transaction(callable $callback): mixed
  {
    $this->beginTransaction();

    try {
      $result = $callback($this->connection);
      $this->commit();
      return $result;
    } catch (\Throwable $e) {
      $this->rollBack();
      throw $e;
    }
  }
  public function beginTransaction(): void
  {
    $this->connection->beginTransaction();
  }

  public function commit(): void
  {
    $this->connection->commit();
  }

  public function rollBack(): void
  {
    $this->connection->rollBack();
  }
}
