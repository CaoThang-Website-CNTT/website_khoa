<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['flash_message'])):
  $msg = $_SESSION['flash_message'];
?>
  <div id="flash-popup" class="popup <?= 'popup--' . $msg['type'] ?>">
    <p><?= $msg['content'] ?></p>
  </div>

  <style>
    .popup {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 16px;
      border-radius: 6px;
      z-index: 9999;
      color: white;
    }

    .popup--success {
      background-color: #2ecc71;
    }

    .popup--error {
      background-color: #e74c3c;
    }
  </style>

  <script>
    setTimeout(() => {
      document.getElementById('flash-popup').style.display = 'none';
    }, 3000);
  </script>

<?php
  unset($_SESSION['flash_message']);
endif;
?>