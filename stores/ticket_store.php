<?php

namespace App\Stores;

use App\Core\Store;
use App\Models\Ticket;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;

interface ITicketStore
{
  /** @return Ticket[] */
  public function getPaginated(int $pageTo, int $limit = 15, string $search = '%'): array;
  public function getById(int $id): ?Ticket;
  public function create(Ticket $ticket): ?Ticket;
  public function update(Ticket $ticket): bool;
  public function delete(int $id): bool;
  public function getTotalCount(?string $search = null): int;
}

class TicketStore extends Store implements ITicketStore
{
  /** @return Ticket[] */
  public function getPaginated(int $pageTo, int $limit = 15, string $search = '%'): array
  {
    $offset = (max(1, $pageTo) - 1) * $limit;

    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder
      ->select("*")
      ->from("tickets")
      ->like("title", $search)
      ->order("id")
      ->range($offset, $offset + $limit - 1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map(
      fn(array $row) => Ticket::fromArray($row),
      $stmt->fetchAll(\PDO::FETCH_ASSOC),
    );
  }

  public function getById(int $id): ?Ticket
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder
      ->select("*")
      ->from("tickets")
      ->eq("id", $id)
      ->limit(1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);

    return $row ? Ticket::fromArray($row) : null;
  }

  public function create(Ticket $ticket): ?Ticket
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder
      ->from("tickets")
      ->insert([
        'title' => $ticket->title,
        'description' => $ticket->description,
        'type' => $ticket->type,
        'status' => $ticket->status,
        'reporter_email' => $ticket->reporter_email,
      ]);

    $stmt = $this->db->prepare($query->toSql());
    $success = $stmt->execute($query->getBindings());

    if (!$success) {
      throw new \Exception('Unable to create ticket.');
    }

    $ticket->id = (int) $this->db->lastInsertId();

    return $ticket;
  }

  public function update(Ticket $ticket): bool
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder
      ->from("tickets")
      ->eq("id", $ticket->id)
      ->update([
        'title' => $ticket->title,
        'description' => $ticket->description,
        'type' => $ticket->type,
        'status' => $ticket->status,
        'reporter_email' => $ticket->reporter_email,
        'updated_at' => (new \DateTime())->format('Y-m-d H:i:s')
      ]);

    $stmt = $this->db->prepare($query->toSql());
    return $stmt->execute($query->getBindings());
  }

  public function delete(int $id): bool
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder
      ->from("tickets")
      ->eq("id", $id)
      ->delete();

    $stmt = $this->db->prepare($query->toSql());
    return $stmt->execute($query->getBindings());
  }

  public function getTotalCount(?string $search = null): int
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder
      ->select("COUNT(*)")
      ->from("tickets");

    if ($search !== null && $search !== '') {
      $query->like("title", $search);
    }

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return (int) $stmt->fetchColumn();
  }
}