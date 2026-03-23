<?php
function jsonResponse($data = [], $message = '', $success = true, $errors = [])
{
  header('Content-Type: application/json');

  echo json_encode([
    'success' => $success,
    'data' => $data,
    'message' => $message,
    'errors' => $errors
  ]);
  exit;
}