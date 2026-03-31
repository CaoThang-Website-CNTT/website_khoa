<?php
namespace App\Services;

require_once BASE_PATH . '/stores/classroom_store.php';
require_once BASE_PATH . '/models/classroom.php';
require_once BASE_PATH . '/models/major.php';
require_once BASE_PATH . '/models/specialization.php';
require_once BASE_PATH . '/includes/core/pageable.php';

use App\Stores\ClassroomStore;
use App\Models\{Classroom, Major, Specialization};
use App\Core\Pageable;

interface IClassroomService
{
  /** @return Classroom[] */
  public function getAllClassrooms(): array;
  public function getClassroomsPaginated(int $page, int $limit = 15): Pageable;
  public function getClassroomById(int $id): ?Classroom;
  public function createClassroom(array $data): ?Classroom;
  public function updateClassroom(int $id, array $data): bool;
  public function deleteClassroom(int $id): bool;
  public function isShortNameUnique(string $shortName, ?int $excludeId = null): bool;
  /** @return Major[] */
  public function getAllMajors(): array;
  public function getMajorById(int $id): ?Major;
  /** @return Specialization[] */
  public function getAllSpecializations(): array;
  /** @return Specialization[] */
  public function getSpecializationsByMajorId(int $majorId): array;
  public function getSpecializationById(int $id): ?Specialization;
}

class ClassroomService implements IClassroomService
{
  private ClassroomStore $_classroomStore;
  public function __construct(ClassroomStore $classroomStore)
  {
    $this->_classroomStore = $classroomStore;
  }
  /** @return Classroom[] */
  public function getAllClassrooms(): array
  {
    return $this->_classroomStore->getAll();
  }
  public function getClassroomsPaginated(int $page, int $limit = 15): Pageable
  {
    $classrooms = $this->_classroomStore->getPaginated($page, $limit);

    // Eager load Major, Specialization
    $majorIds = array_filter(array_column($classrooms, 'major_id'));
    $specializationIds = array_filter(array_column($classrooms, 'specialization_id'));

    $majorMap = array_column($this->_classroomStore->getByMajorIds($majorIds), null, 'id');
    $specializationMap = array_column($this->_classroomStore->getBySpecializationIds(array_values($specializationIds)), null, 'id');

    foreach ($classrooms as $classroom) {
      if ($classroom->major_id && isset($majorMap[$classroom->major_id])) {
        $classroom->major = $majorMap[$classroom->major_id];
      }
      if ($classroom->specialization_id && isset($specializationMap[$classroom->specialization_id])) {
        $classroom->specialization = $specializationMap[$classroom->specialization_id];
      }
    }

    $total = $this->_classroomStore->getTotalCount();
    return new Pageable($classrooms, $total, $limit, $page);
  }
  public function getClassroomById(int $id): ?Classroom
  {
    return $this->_classroomStore->getById($id);
  }
  public function createClassroom(array $data): ?Classroom
  {
    $major = $this->_classroomStore->getMajorById((int) $data['major_id']);
    if (!$major) {
      throw new \InvalidArgumentException('Ngành học không tồn tại.');
    }
    $data['major_level'] = $major->level;
    $data['major_short'] = $major->short_name;
    $classCode = $this->_buildClassCode($data);
    if (!$classCode) {
      throw new \InvalidArgumentException('Mã lớp không hợp lệ. Vui lòng kiểm tra lại ngành/chuyên ngành/năm học.');
    }
    $classroom = new Classroom(
      major_id: (int) $data['major_id'],
      specialization_id: !empty($data['specialization_id']) ? (int) $data['specialization_id'] : null,
      class_of: (int) $data['class_of'],
      letter: $data['letter'] ?? '',
      short_name: $classCode,
      homeroom_teacher_id: !empty($data['homeroom_teacher_id']) ? (int) $data['homeroom_teacher_id'] : null,
    );
    return $this->_classroomStore->create($classroom);
  }
  public function updateClassroom(int $id, array $data): bool
  {
    $classroom = $this->_classroomStore->getById($id);
    if ($classroom === null) {
      throw new \InvalidArgumentException('Lớp học không tồn tại.');
    }

    $needRebuildCode = false;

    // Nếu major_id được cập nhật, cần lấy thông tin major để build lại mã lớp
    if (isset($data['major_id']) && $data['major_id'] != $classroom->major_id) {
      $major = $this->_classroomStore->getMajorById((int) $data['major_id']);
      if (!$major) {
        throw new \InvalidArgumentException('Ngành học không tồn tại.');
      }
      $data['major_level'] = $major->level;
      $data['major_short'] = $major->short_name;

      $needRebuildCode = true;
    }

    // Nếu specialization_id được cập nhật, cần lấy thông tin specialization để build lại mã lớp
    if (isset($data['specialization_id']) && $data['specialization_id'] != $classroom->specialization_id) {
      if (!empty($data['specialization_id'])) {
        $spec = $this->_classroomStore->getSpecializationById((int) $data['specialization_id']);
        if (!$spec) {
          throw new \InvalidArgumentException('Chuyên ngành không tồn tại.');
        }
        $data['major_short'] = $spec->short_name . ($data['major_short'] ?? '');
      }

      $needRebuildCode = true;
    }

    $classCode = $needRebuildCode ? $this->_buildClassCode($data) ?? '' : $classroom->short_name;

    $classroom->major_id = (int) $data['major_id'];
    $classroom->specialization_id = !empty($data['specialization_id']) ? (int) $data['specialization_id'] : null;
    $classroom->class_of = (int) $data['class_of'];
    $classroom->letter = $data['letter'] ?? '';
    $classroom->short_name = $classCode;
    $classroom->homeroom_teacher_id = !empty($data['homeroom_teacher_id']) ? (int) $data['homeroom_teacher_id'] : null;


    return $this->_classroomStore->update($classroom);
  }
  public function deleteClassroom(int $id): bool
  {
    return $this->_classroomStore->softDelete($id);
  }
  public function isShortNameUnique(string $shortName, ?int $excludeId = null): bool
  {
    return $this->_classroomStore->isShortNameUnique($shortName, $excludeId);
  }
  /** @return Major[] */
  public function getAllMajors(): array
  {
    return $this->_classroomStore->getAllMajors();
  }
  public function getMajorById(int $id): ?Major
  {
    return $this->_classroomStore->getMajorById($id);
  }
  /** @return Specialization[] */
  public function getAllSpecializations(): array
  {
    return $this->_classroomStore->getAllSpecializations();
  }
  /** @return Specialization[] */
  public function getSpecializationsByMajorId(int $majorId): array
  {
    return $this->_classroomStore->getSpecializationsByMajorId($majorId);
  }
  public function getSpecializationById(int $id): ?Specialization
  {
    return $this->_classroomStore->getSpecializationById($id);
  }
  private function _buildClassCode(array $data): ?string
  {
    $level = trim($data['major_level'] ?? '');
    $majorShort = trim($data['major_short'] ?? '');
    $specId = $data['specialization_id'] ?? null;
    $year = trim($data['class_of'] ?? '');
    $letter = strtoupper(trim($data['letter'] ?? ''));
    $shortName = $majorShort;
    if ($specId) {
      $spec = $this->_classroomStore->getSpecializationById((int) $specId);
      if (!$spec) {
        return null;
      }
      $shortName = $spec->short_name . $majorShort;
    }
    if (empty($level) || empty($year) || empty($shortName)) {
      return null;
    }
    if (strlen($year) !== 4 || !is_numeric($year)) {
      return null;
    }
    $letter = substr(preg_replace('/[^A-Z]/', '', $letter), 0, 1);
    return $level . $shortName . $year . $letter;
  }
}