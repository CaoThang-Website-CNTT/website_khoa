<?php

namespace App\Stores;

use App\Core\Store;
use App\Models\Media;
use App\Core\Schema\QueryBuilder;
use App\Core\Schema\Compiler\MySQLCompiler;

interface IMediaStore
{
  public function create(Media $media): Media;
  public function findById(int $id): ?Media;
  public function findByPostId(int $postId): array;
  public function findOrphansOlderThan(\DateTimeInterface $cutoff): array;
  public function update(int $id, array $data): Media;
  public function attachToPost(array $mediaIds, int $postId): void;
  public function delete(int $id): void;
}

class MediaStore extends Store implements IMediaStore
{
  public function create(Media $media): Media
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder->from('media')->insert([
      'file_name' => $media->file_name,
      'file_path' => $media->file_path,
      'mime_type' => $media->mime_type,
      'file_size' => $media->file_size,
      'alt_text' => $media->alt_text,
      'post_id' => $media->post_id,
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

  public function findById(int $id): ?Media
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder->from('media')->select('*')->eq('id', $id)->limit(1);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
    return $row ? Media::fromArray($row) : null;
  }

  public function findByPostId(int $postId): array
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder->from('media')
      ->select('*')
      ->eq('post_id', $postId)
      ->order('created_at', ['ascending' => true]);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map(
      static fn(array $row) => Media::fromArray($row),
      $stmt->fetchAll(\PDO::FETCH_ASSOC),
    );
  }

  public function findOrphansOlderThan(\DateTimeInterface $cutoff): array
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder->from('media')
      ->select('*')
      ->eq('post_id', null)
      ->lt('created_at', $cutoff->format('Y-m-d H:i:s'));

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return array_map(
      static fn(array $row) => Media::fromArray($row),
      $stmt->fetchAll(\PDO::FETCH_ASSOC),
    );
  }

  public function update(int $id, array $data): Media
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    // Chặn các field immutable
    $immutable = ['id', 'file_path', 'created_at'];
    $data = array_diff_key($data, array_flip($immutable));

    if (empty($data)) {
      return $this->findById($id) ?? throw new \RuntimeException("Media #{$id} không tồn tại.");
    }

    $data['updated_at'] = (new \DateTime())->format('Y-m-d H:i:s');

    $query = $builder->from('media')->update($data)->eq('id', $id);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());

    return $this->findById($id) ?? throw new \RuntimeException("Media #{$id} không tồn tại sau khi cập nhật.");
  }

  public function attachToPost(array $mediaIds, int $postId): void
  {
    if (empty($mediaIds)) {
      return;
    }
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder->from('media')
      ->update(['post_id' => $postId])
      ->in('id', $mediaIds);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
  }

  public function delete(int $id): void
  {
    $builder = new QueryBuilder(new MySQLCompiler());

    $query = $builder->from('media')->delete()->eq('id', $id);

    $stmt = $this->db->prepare($query->toSql());
    $stmt->execute($query->getBindings());
  }
}