<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once BASE_PATH . '/includes/mail/PHPMailer/Exception.php';
require_once BASE_PATH . '/includes/mail/PHPMailer/PHPMailer.php';
require_once BASE_PATH . '/includes/mail/PHPMailer/SMTP.php';

class MailService
{
  private ?string $host;
  private ?int $port;
  private ?string $username;
  private ?string $password;
  private ?string $fromName;

  public function __construct()
  {
    $this->host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
    $this->port = isset($_ENV['MAIL_PORT']) ? (int)$_ENV['MAIL_PORT'] : 465;
    $this->username = $_ENV['MAIL_USERNAME'] ?? '';
    $this->password = $_ENV['MAIL_PASSWORD'] ?? '';
    $this->fromName = $_ENV['MAIL_FROM_NAME'] ?? 'Hệ thống Quản lý Thực tập';
  }

  /**
   * Khởi tạo đối tượng PHPMailer với cấu hình mặc định
   */
  private function createMailer(): PHPMailer
  {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $this->host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $this->username;
    $mail->Password   = $this->password;
    $mail->SMTPSecure = $this->port === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $this->port;
    $mail->CharSet    = 'UTF-8';

    // Cấu hình ngôn ngữ tiếng Việt (tùy chọn)
    $mail->setLanguage('vi');

    $mail->setFrom($this->username, $this->fromName);
    return $mail;
  }

  /**
   * Gửi email thông báo thay đổi Giảng viên hướng dẫn
   * @param string $recipientEmail Email người nhận
   * @param string $recipientName Tên người nhận (Sinh viên hoặc Giảng viên)
   * @param array $details Mảng chứa chi tiết: studentName, oldTeacherName, newTeacherName
   */
  public function sendReassignNotification(
    string $recipientEmail,
    string $recipientName,
    array $details
  ): bool {
    if (empty($this->username) || empty($this->password)) {
      // Chưa cấu hình email
      return false;
    }

    if (empty($recipientEmail)) {
      return false;
    }

    try {
      $mail = $this->createMailer();
      $mail->addAddress($recipientEmail, $recipientName);

      $mail->isHTML(true);
      $mail->Subject = '[Khoa CNTT] Thông báo Thay đổi Giảng viên hướng dẫn thực tập';

      $studentName = $details['studentName'] ?? 'Sinh viên';
      $mssv = $details['mssv'] ?? 'Không rõ';
      $batchTitle = $details['batchTitle'] ?? 'Không rõ';
      $startAt = $details['startAt'] ?? 'Không rõ';
      $endAt = $details['endAt'] ?? 'Không rõ';
      $oldTeacherName = $details['oldTeacherName'] ?? 'Không có';
      $newTeacherName = $details['newTeacherName'] ?? 'Không có';

      $logoPath = BASE_PATH . '/public/img/faculty_logo.jpg';
      if (file_exists($logoPath)) {
        $mail->addEmbeddedImage($logoPath, 'faculty_logo');
        $logoUrl = 'cid:faculty_logo';
      } else {
        $logoUrl = url('public/img/faculty_logo.jpg');
      }

      $portalUrl = url('portal');

      $body = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 640px; margin: 0 auto; border: 1px solid #ddd; border-radius: 5px; overflow: hidden;'>
                <div style='background-color: #f8f9fa; padding: 20px; text-align: center; border-bottom: 1px solid #ddd;'>
                    <img src='{$logoUrl}' alt='Logo Khoa CNTT' style='max-width: 32px; height: auto;' />
                    <h2 style='color: #155dfc; margin-top: 10px; margin-bottom: 0;'>Khoa Công Nghệ Thông Tin</h2>
                </div>
                <div style='padding: 20px;'>
                    <p>Xin chào <strong>{$recipientName}</strong>,</p>
                    <p>Hệ thống Quản lý Thực tập xin thông báo về việc thay đổi Giảng viên hướng dẫn. Thông tin chi tiết như sau:</p>
                    <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd; background-color: #f4f4f5; font-weight: bold; width: 40%;'>Đợt thực tập:</td>
                            <td style='padding: 8px; border: 1px solid #ddd;'>{$batchTitle}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd; background-color: #f4f4f5; font-weight: bold;'>Thời gian:</td>
                            <td style='padding: 8px; border: 1px solid #ddd;'>{$startAt} - {$endAt}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd; background-color: #f4f4f5; font-weight: bold;'>MSSV:</td>
                            <td style='padding: 8px; border: 1px solid #ddd;'>{$mssv}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd; background-color: #f4f4f5; font-weight: bold;'>Sinh viên thực tập:</td>
                            <td style='padding: 8px; border: 1px solid #ddd;'>{$studentName}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd; background-color: #f4f4f5; font-weight: bold;'>Giảng viên cũ:</td>
                            <td style='padding: 8px; border: 1px solid #ddd;'>{$oldTeacherName}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd; background-color: #f4f4f5; font-weight: bold;'>Giảng viên mới:</td>
                            <td style='padding: 8px; border: 1px solid #ddd; color: #d9534f; font-weight: bold;'>{$newTeacherName}</td>
                        </tr>
                    </table>
                    <p>Thông tin liên hệ chi tiết xem <a href='{$portalUrl}' style='color: #155dfc; text-decoration: underline; font-weight: bold;'>tại đây</a>.</p>
                    <p>Nếu có thắc mắc, vui lòng liên hệ với ban quản lý Khoa CNTT.</p>
                    <p>Trân trọng,<br><strong>Ban Quản lý Hệ thống Thực tập</strong></p>
                </div>
            </div>";

      $mail->Body = $body;
      $mail->AltBody = "Xin chào {$recipientName},\n\nHệ thống thông báo thay đổi Giảng viên hướng dẫn:\nĐợt thực tập: {$batchTitle} ({$startAt} - {$endAt})\nSinh viên: {$studentName} - {$mssv}\nGiảng viên cũ: {$oldTeacherName}\nGiảng viên mới: {$newTeacherName}\n\nThông tin liên hệ chi tiết xem tại đây: {$portalUrl}\n\nTrân trọng, Ban Quản lý Hệ thống Thực tập.";

      $mail->send();
      return true;
    } catch (Exception $e) {
      error_log("Lỗi gửi email: {$e->getMessage()}");
      return false;
    }
  }
}
