<?php

namespace App\Stores;

use App\Core\Store;
use App\Models\Media;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;

interface IMediaStore
{
  // C
  public function create(Media $media): Media;

  // R
  /** @return Media[] */
  public function getPaginated(int $page, int $perPage, ?string $search = null): array;
  public function getTotalCount(?string $search = null): int;
  public function getById(int $id): ?Media;
  /** @return Media[] */
  public function getByIds(array $ids): array;

  // U
  public function update(int $id, array $data): Media;

  // D
  public function delete(int $id): void;
}

class MediaStore extends Store implements IMediaStore
{
  public function create(Media $media): Media
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder->from('media')->insert([
      'title' => $media->title,
      'file_name' => $media->file_name,
      'file_path' => $media->file_path,
      'mime_type' => $media->mime_type,
      'alt_text' => $media->alt_text,
      'width' => $media->width,
      'height' => $media->height,
      'file_size' => $media->file_size,
      'metadata' => $media->metadata ? json_encode($media->metadata) : null,
      'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
      'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
    ]);

    $stmt = $this->db->prepare($query->toSql());
    $success = $stmt->execute($query->getBindings());

    if (!$success) {
      throw new \Exception('Không thể lưu hình ảnh vào cơ sở dữ liệu.');
    }

    $media->id = (int) $this->db->lastInsertId();
    return $media;
  }

    public function getPaginated(int $pageTo, int $limit = 15, ?string $search = null): array
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $builder->from('media')
      ->select('*')
      ->order('created_at', ['ascending' => false]);

    $builder->range(($pageTo - 1) * $limit, $pageTo * $limit - 1);

    if ($search !== null && $search !== '') {
      $builder->like('file_name', '%' . $search . '%');
    }

    $stmt = $this->db->prepare($builder->toSql());
    $stmt->execute($builder->getBindings());

    return array_map(function (array $row) {
      if (isset($row['metadata']) && is_string($row['metadata'])) {
        $row['metadata'] = json_decode($row['metadata'], true);
      }
      return Media::fromArray($row);
    }, $stmt->fetchAll(\PDO::FETCH_ASSOC));
  }

  public function getTotalCount(?string $search = null): int
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $builder->from('media')->select('COUNT(*) AS total');

    if ($search !== null && $search !== '') {
      $builder->like('file_name', '%' . $search . '%');
    }

    $stmt = $this->db->prepare($builder->toSql());
    $stmt->execute($builder->getBindings());

    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $row ? (int) $row['total'] : 0;
  }

  public function getById(int $id): ?Media
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder->from('media')->select('*')->eq('id', $id)->limit(1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    if ($row && isset($row['metadata']) && is_string($row['metadata'])) {
      $row['metadata'] = json_decode($row['metadata'], true);
    }
    return $row ? Media::fromArray($row) : null;
  }

  /** @return Media[] */
  public function getByIds(array $ids): array
  {
    if (empty($ids)) {
      return [];
    }
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder->from('media')
      ->select('*')
      ->in('id', $ids);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map(function (array $row) {
      if (isset($row['metadata']) && is_string($row['metadata'])) {
        $row['metadata'] = json_decode($row['metadata'], true);
      }
      return Media::fromArray($row);
    }, $stmt->fetchAll(\PDO::FETCH_ASSOC));
  }

  public function update(int $id, array $data): Media
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $immutable = ['id', 'file_path', 'created_at'];
    $data = array_diff_key($data, array_flip($immutable));

    if (empty($data)) {
      return $this->getById($id) ?? throw new \RuntimeException("Media #{$id} không tồn tại.");
    }

    if (array_key_exists('metadata', $data)) {
      $data['metadata'] = $data['metadata'] ? json_encode($data['metadata']) : null;
    }

    $data['updated_at'] = (new \DateTime())->format('Y-m-d H:i:s');

    $query = $builder->from('media')->update($data)->eq('id', $id);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return $this->getById($id) ?? throw new \RuntimeException("Media #{$id} không tồn tại sau khi cập nhật.");
  }

  public function delete(int $id): void
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder->from('media')->delete()->eq('id', $id);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
  }
}