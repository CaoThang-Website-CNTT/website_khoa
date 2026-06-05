<?php
namespace App\Middlewares\Traits;

trait HasDashboardRouting
{
  protected function dashboardFor(?string $role): string
  {
    return match ($role) {
      'admin', 'editor', 'super_admin' => '/admin',
      'student' => '/student',
      'teacher' => '/teacher',
      default => '/',
    };
  }
}