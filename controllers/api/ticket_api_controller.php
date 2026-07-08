<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Services\TicketService;
use Exception;

class TicketApiController extends Controller
{
  public function __construct(private TicketService $service) {}

  public function index(Request $request)
  {
    $search = trim((string)$request->query('search', ''));
    try {
      return $this->json($this->service->getTickets(
        max(1, (int)$request->query('page', 1)),
        min(100, max(1, (int)$request->query('limit', 15))),
        $search === '' ? '%' : "%{$search}%"
      ), 200);
    } catch (Exception $e) {
      return $this->json(null, 500, $e->getMessage());
    }
  }
}
