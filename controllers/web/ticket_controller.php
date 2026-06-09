<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\RequestValidator;
use App\Models\Ticket;
use App\Services\TicketService;

class TicketController extends Controller
{
  private TicketService $_ticketService;

  public function __construct(TicketService $ticketService)
  {
    $this->_ticketService = $ticketService;
  }

  public function index(Request $request)
  {
    $currentPage = (int) $request->query('page', 1);
    $limit = (int) $request->query('limit', 15);
    $search = $request->query("search", '%');

    $data = $this->_ticketService->getTickets($currentPage, $limit, $search ?: null);

    $this->render('admin/tickets/index', [
      'data' => $data,
      'search' => $search,
    ], layout: 'dashboard_layout');
  }

  public function create()
  {
    $this->render('admin/tickets/create', [
      'types' => Ticket::TYPES,
    ], layout: 'dashboard_layout');
  }

  public function store(Request $request)
  {
    $data = $request->all();
    $validator = new RequestValidator();
    $rules = [
      'title' => ['required', 'max:255'],
      'description' => ['required'],
      'type' => ['required'],
      'reporter_email' => ['required', 'email', 'max:100'],
    ];

    if (!$validator->validate($data, $rules) || !in_array($data['type'] ?? '', Ticket::TYPES, true)) {
      if (!in_array($data['type'] ?? '', Ticket::TYPES, true)) {
        $validator->addError('type', 'Loại phản hồi không hợp lệ.');
      }
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/tickets/create');
    }

    $ticket = $this->_ticketService->createTicket($data);

    if ($ticket) {
      $request->session()->flashNotify('success', 'Tạo phản hồi thành công.');
      return $this->redirect('admin/tickets/create');
    }

    $request->session()->flashNotify('error', 'Không thể tạo phản hồi mới.');
    return $this->redirect('admin/tickets/create');
  }

  public function show(int $ticket_id)
  {
    $ticket = $this->_ticketService->getTicketById($ticket_id);

    if (!$ticket) {
      return $this->abort(404);
    }

    $this->render('admin/tickets/edit', [
      'ticket' => $ticket,
      'statuses' => Ticket::STATUSES,
    ], layout: 'dashboard_layout');
  }

  public function edit(int $ticket_id)
  {
    $ticket = $this->_ticketService->getTicketById($ticket_id);

    if (!$ticket) {
      return $this->abort(404);
    }

    $this->render('admin/tickets/edit', [
      'ticket' => $ticket,
      'statuses' => Ticket::STATUSES,
    ], layout: 'dashboard_layout');
  }

  public function update(int $ticket_id, Request $request)
  {
    $data = $request->all();
    $validator = new RequestValidator();
    $rules = [
      'status' => ['required'],
    ];

    if (!$validator->validate($data, $rules) || !in_array($data['status'] ?? '', Ticket::STATUSES, true)) {
      if (!in_array($data['status'] ?? '', Ticket::STATUSES, true)) {
        $validator->addError('status', 'Trạng thái phản hồi không hợp lệ.');
      }
      $request->flashOldInputs();
      $request->session()->flashErrors($validator->getErrors());
      return $this->redirect('admin/tickets/' . $ticket_id . '/edit');
    }

    $isSuccess = $this->_ticketService->updateTicketStatus($ticket_id, $data['status']);

    if ($isSuccess) {
      $request->session()->flashNotify('success', 'Cập nhật trạng thái phản hồi thành công.');
    } else {
      $request->session()->flashNotify('error', 'Không thể cập nhật trạng thái yêu cầu.');
    }

    return $this->redirect('admin/tickets/' . $ticket_id);
  }
}