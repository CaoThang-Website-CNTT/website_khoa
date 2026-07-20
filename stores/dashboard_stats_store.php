<?php

namespace App\Stores;

use App\Core\Schema\Compiler\MySQLCompiler;
use App\Core\Schema\QueryBuilder;
use App\Core\Store;
use PDO;
use Throwable;

interface IDashboardStatsStore
{
  public function getCmsOverview(): ?array;
  public function getInternshipOverview(): ?array;
  public function getProjectOverview(): ?array;
  public function getRecentActivity(): array;
}

class DashboardStatsStore extends Store implements IDashboardStatsStore
{
  public function getCmsOverview(): ?array
  {
    if (!$this->hasTable('posts')) {
      return null;
    }

    try {
      $total = $this->countByBuilder(
        $this->query()
          ->from('posts')
          ->select('COUNT(*)')
          ->is('deleted_at', null)
      );

      $published = $this->countByBuilder(
        $this->query()
          ->from('posts')
          ->select('COUNT(*)')
          ->is('deleted_at', null)
          ->eq('status', 'published')
      );

      $draft = $this->countByBuilder(
        $this->query()
          ->from('posts')
          ->select('COUNT(*)')
          ->is('deleted_at', null)
          ->eq('status', 'draft')
      );

      $newestPublished = $this->fetchAllByBuilder(
        $this->query()
          ->from('posts')
          ->select('id', 'title', 'slug', 'published_at', 'updated_at')
          ->is('deleted_at', null)
          ->eq('status', 'published')
          ->order('published_at', ['ascending' => false])
          ->order('id', ['ascending' => false])
          ->limit(3)
      );

      $mostViewed = $this->fetchAllByBuilder(
        $this->query()
          ->from('posts')
          ->select('id', 'title', 'slug', 'view_count', 'published_at')
          ->is('deleted_at', null)
          ->eq('status', 'published')
          ->order('view_count', ['ascending' => false])
          ->order('published_at', ['ascending' => false])
          ->limit(3)
      );

      if ($total <= 0) {
        return null;
      }

      return [
        'total' => $total,
        'published' => $published,
        'draft' => $draft,
        'newest_posts' => $newestPublished,
        'most_viewed_posts' => $mostViewed,
      ];
    } catch (Throwable) {
      return null;
    }
  }

  public function getInternshipOverview(): ?array
  {
    if (!$this->hasTable('internship_batches')) {
      return null;
    }

    try {
      $activeBatches = $this->fetchAllByBuilder(
        $this->query()
          ->from('internship_batches')
          ->select('id', 'title', 'start_at', 'end_at', 'status', 'updated_at')
          ->is('deleted_at', null)
          ->eq('status', 'published')
          ->order('end_at')
          ->order('updated_at', ['ascending' => false])
          ->limit(5)
      );

      $activeCount = $this->countByBuilder(
        $this->query()
          ->from('internship_batches')
          ->select('COUNT(*)')
          ->is('deleted_at', null)
          ->eq('status', 'published')
      );

      if ($activeCount <= 0) {
        return null;
      }

      $students = $this->safeCount(
        ['internship_batch_students'],
        $this->query()
          ->from('internship_batch_students')
          ->select('COUNT(*)')
          ->join('internship_batches', 'internship_batches.id', '=', 'internship_batch_students.batch_id')
          ->is('internship_batches.deleted_at', null)
          ->eq('internship_batches.status', 'published')
      );

      $withCompany = $this->safeCount(
        ['internship_batch_students'],
        $this->query()
          ->from('internship_batch_students')
          ->select('COUNT(*)')
          ->join('internship_batches', 'internship_batches.id', '=', 'internship_batch_students.batch_id')
          ->is('internship_batches.deleted_at', null)
          ->eq('internship_batches.status', 'published')
          ->is('internship_batch_students.company_id', 'NOT NULL')
      );

      $assigned = $this->safeCount(
        ['internship_batch_students', 'internship_assignments'],
        $this->query()
          ->from('internship_batch_students')
          ->select('COUNT(DISTINCT internship_batch_students.id)')
          ->join('internship_batches', 'internship_batches.id', '=', 'internship_batch_students.batch_id')
          ->join('internship_assignments', 'internship_assignments.batch_student_id', '=', 'internship_batch_students.id')
          ->is('internship_batches.deleted_at', null)
          ->eq('internship_batches.status', 'published')
      );

      $submitted = $this->safeCount(
        ['internship_batch_students', 'internship_submissions'],
        $this->query()
          ->from('internship_batch_students')
          ->select('COUNT(DISTINCT internship_batch_students.id)')
          ->join('internship_batches', 'internship_batches.id', '=', 'internship_batch_students.batch_id')
          ->join('internship_submissions', 'internship_submissions.batch_student_id', '=', 'internship_batch_students.id')
          ->is('internship_batches.deleted_at', null)
          ->eq('internship_batches.status', 'published')
      );

      $graded = $this->safeCount(
        ['internship_batch_students', 'internship_grades'],
        $this->query()
          ->from('internship_batch_students')
          ->select('COUNT(DISTINCT internship_batch_students.id)')
          ->join('internship_batches', 'internship_batches.id', '=', 'internship_batch_students.batch_id')
          ->join('internship_grades', 'internship_grades.batch_student_id', '=', 'internship_batch_students.id')
          ->is('internship_batches.deleted_at', null)
          ->eq('internship_batches.status', 'published')
          ->is('internship_grades.grade_lock_at', 'NOT NULL')
      );

      $pendingReferrals = $this->safeCount(
        ['internship_batch_students', 'referral_letters'],
        $this->query()
          ->from('referral_letters')
          ->select('COUNT(*)')
          ->join('internship_batch_students', 'internship_batch_students.id', '=', 'referral_letters.batch_student_id')
          ->join('internship_batches', 'internship_batches.id', '=', 'internship_batch_students.batch_id')
          ->is('internship_batches.deleted_at', null)
          ->eq('internship_batches.status', 'published')
          ->eq('referral_letters.status', 'pending')
      );

      return [
        'active_count' => $activeCount,
        'active_batches' => $activeBatches,
        'students' => $students,
        'with_company' => $withCompany,
        'assigned' => $assigned,
        'submitted' => $submitted,
        'graded' => $graded,
        'pending_referrals' => $pendingReferrals,
      ];
    } catch (Throwable) {
      return null;
    }
  }

  public function getProjectOverview(): ?array
  {
    if (!$this->hasTable('project_batches')) {
      return null;
    }

    try {
      $activeBatches = $this->fetchAllByBuilder(
        $this->query()
          ->from('project_batches')
          ->select('id', 'title', 'registration_start', 'registration_end', 'status', 'updated_at')
          ->is('deleted_at', null)
          ->eq('status', 'published')
          ->order('registration_end')
          ->order('updated_at', ['ascending' => false])
          ->limit(5)
      );

      $activeCount = $this->countByBuilder(
        $this->query()
          ->from('project_batches')
          ->select('COUNT(*)')
          ->is('deleted_at', null)
          ->eq('status', 'published')
      );

      if ($activeCount <= 0) {
        return null;
      }

      $totalTopics = $this->safeCount(
        ['project_topics'],
        $this->query()
          ->from('project_topics')
          ->select('COUNT(*)')
          ->join('project_batches', 'project_batches.id', '=', 'project_topics.batch_id')
          ->is('project_batches.deleted_at', null)
          ->eq('project_batches.status', 'published')
          ->is('project_topics.deleted_at', null)
      );

      $approvedTopics = $this->safeCount(
        ['project_topics'],
        $this->query()
          ->from('project_topics')
          ->select('COUNT(*)')
          ->join('project_batches', 'project_batches.id', '=', 'project_topics.batch_id')
          ->is('project_batches.deleted_at', null)
          ->eq('project_batches.status', 'published')
          ->is('project_topics.deleted_at', null)
          ->eq('project_topics.status', 'approved')
      );

      $pendingTopics = $this->safeCount(
        ['project_topics'],
        $this->query()
          ->from('project_topics')
          ->select('COUNT(*)')
          ->join('project_batches', 'project_batches.id', '=', 'project_topics.batch_id')
          ->is('project_batches.deleted_at', null)
          ->eq('project_batches.status', 'published')
          ->is('project_topics.deleted_at', null)
          ->eq('project_topics.status', 'pending')
      );

      $groups = $this->safeCount(
        ['project_groups'],
        $this->query()
          ->from('project_groups')
          ->select('COUNT(*)')
          ->join('project_batches', 'project_batches.id', '=', 'project_groups.batch_id')
          ->is('project_batches.deleted_at', null)
          ->eq('project_batches.status', 'published')
      );

      $allocatedGroups = $this->safeCount(
        ['project_groups'],
        $this->query()
          ->from('project_groups')
          ->select('COUNT(*)')
          ->join('project_batches', 'project_batches.id', '=', 'project_groups.batch_id')
          ->is('project_batches.deleted_at', null)
          ->eq('project_batches.status', 'published')
          ->is('project_groups.assigned_topic_id', 'NOT NULL')
      );

      $publishedAllocations = $this->safeCount(
        ['project_batches'],
        $this->query()
          ->from('project_batches')
          ->select('COUNT(*)')
          ->is('deleted_at', null)
          ->eq('status', 'published')
          ->is('allocation_published_at', 'NOT NULL')
      );

      return [
        'active_count' => $activeCount,
        'active_batches' => $activeBatches,
        'total_topics' => $totalTopics,
        'approved_topics' => $approvedTopics,
        'pending_topics' => $pendingTopics,
        'groups' => $groups,
        'allocated_groups' => $allocatedGroups,
        'published_allocations' => $publishedAllocations,
      ];
    } catch (Throwable) {
      return null;
    }
  }

  public function getRecentActivity(): array
  {
    $items = [];

    if ($this->hasTable('posts')) {
      $row = $this->safeFetchOneByBuilder(
        $this->query()
          ->from('posts')
          ->select('id', 'title AS label', 'updated_at AS happened_at')
          ->is('deleted_at', null)
          ->order('updated_at', ['ascending' => false])
          ->order('id', ['ascending' => false])
          ->limit(1)
      );
      if ($row) {
        $items[] = [
          'domain' => 'CMS',
          'label' => $row['label'],
          'happened_at' => $row['happened_at'],
          'url' => 'admin/posts/' . $row['id'],
        ];
      }
    }

    if ($this->hasTable('internship_batches')) {
      $row = $this->safeFetchOneByBuilder(
        $this->query()
          ->from('internship_batches')
          ->select('id', 'title AS label', 'updated_at AS happened_at')
          ->is('deleted_at', null)
          ->order('updated_at', ['ascending' => false])
          ->order('id', ['ascending' => false])
          ->limit(1)
      );
      if ($row) {
        $items[] = [
          'domain' => 'TTTN',
          'label' => $row['label'],
          'happened_at' => $row['happened_at'],
          'url' => 'admin/internship_batches/' . $row['id'],
        ];
      }
    }

    if ($this->hasTable('project_batches')) {
      $row = $this->safeFetchOneByBuilder(
        $this->query()
          ->from('project_batches')
          ->select('id', 'title AS label', 'updated_at AS happened_at')
          ->is('deleted_at', null)
          ->order('updated_at', ['ascending' => false])
          ->order('id', ['ascending' => false])
          ->limit(1)
      );
      if ($row) {
        $items[] = [
          'domain' => 'DATN',
          'label' => $row['label'],
          'happened_at' => $row['happened_at'],
          'url' => 'admin/project_batches/' . $row['id'],
        ];
      }
    }

    usort($items, fn(array $a, array $b) => strcmp((string) $b['happened_at'], (string) $a['happened_at']));
    return array_slice($items, 0, 5);
  }

  private function hasTable(string $table): bool
  {
    try {
      $stmt = $this->db->prepare(
        "SELECT COUNT(*)
         FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name = :table"
      );
      $stmt->execute([':table' => $table]);
      return (int) $stmt->fetchColumn() > 0;
    } catch (Throwable) {
      return false;
    }
  }

  private function safeCount(array $tables, string|QueryBuilder $query): ?int
  {
    foreach ($tables as $table) {
      if (!$this->hasTable($table)) {
        return null;
      }
    }

    try {
      if ($query instanceof QueryBuilder) {
        return $this->countByBuilder($query);
      }

      return $this->countRows($query);
    } catch (Throwable) {
      return null;
    }
  }

  private function query(): QueryBuilder
  {
    return new QueryBuilder(new MySQLCompiler());
  }

  private function countByBuilder(QueryBuilder $query): int
  {
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return (int) $stmt->fetchColumn();
  }

  private function countRows(string $sql): int
  {
    $stmt = $this->db->query($sql);
    return (int) $stmt->fetchColumn();
  }

  private function fetchOne(string $sql): ?array
  {
    $stmt = $this->db->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
  }

  private function fetchOneByBuilder(QueryBuilder $query): ?array
  {
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
  }

  private function safeFetchOneByBuilder(QueryBuilder $query): ?array
  {
    try {
      return $this->fetchOneByBuilder($query);
    } catch (Throwable) {
      return null;
    }
  }

  private function fetchAll(string $sql): array
  {
    $stmt = $this->db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  private function fetchAllByBuilder(QueryBuilder $query): array
  {
    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
