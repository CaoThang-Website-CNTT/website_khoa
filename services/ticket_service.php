<?php

namespace App\Services;


use App\Core\Pageable;
use App\Models\Ticket;
use App\Stores\TicketStore;

interface ITicketService
{
  public function getTickets(int $page, int $limit = 15, string $search = '%'): Pageable;
  public function getTicketById(int $id): ?Ticket;
  public function createTicket(array $data): ?Ticket;
  public function updateTicket(int $id, array $data): bool;
  public function updateTicketStatus(int $id, string $status): bool;
  public function deleteTicket(int $id): bool;
}

class TicketService implements ITicketService
{
  private TicketStore $_store;

  public function __construct(TicketStore $store)
  {
    $this->_store = $store;
  }

  public function getTickets(int $page, int $limit = 15, string $search = '%'): Pageable
  {
    $tickets = $this->_store->getPaginated($page, $limit, $search);
    $total = $this->_store->getTotalCount($search);

    return new Pageable($tickets, $total, $limit, $page);
  }

  public function getTicketById(int $id): ?Ticket
  {
    return $this->_store->getById($id);
  }

  public function createTicket(array $data): ?Ticket
  {
    $ticket = new Ticket(
      title: trim($data['title'] ?? ''),
      description: trim($data['description'] ?? ''),
      type: $data['type'] ?? 'feedback',
      status: $data['status'] ?? 'pending',
      reporter_email: trim($data['reporter_email'] ?? '')
    );

    return $this->_store->create($ticket);
  }

  public function updateTicket(int $id, array $data): bool
  {
    $ticket = $this->_store->getById($id);

    if (!$ticket) {
      return false;
    }

    $ticket->title = $data['title'] ?? $ticket->title;
    $ticket->description = $data['description'] ?? $ticket->description;
    $ticket->type = $data['type'] ?? $ticket->type;
    $ticket->reporter_email = $data['reporter_email'] ?? $ticket->reporter_email;

    if (array_key_exists('status', $data)) {
      $ticket->status = $data['status'];
    }

    return $this->_store->update($ticket);
  }

  public function updateTicketStatus(int $id, string $status): bool
  {
    $ticket = $this->_store->getById($id);

    if (!$ticket) {
      return false;
    }

    $ticket->status = $status;

    return $this->_store->update($ticket);
  }

  public function deleteTicket(int $id): bool
  {
    return $this->_store->delete($id);
  }
}
