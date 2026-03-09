<?php
// Check if a file was uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploaded_file'])) {
  $handler = new FileHandler();

  $fileError = $_FILES['import_file']['error'];

  try {
    // Process the file
    $parsedData = $handler->processUpload($_FILES['uploaded_file']);

    echo "<h3>File Successfully Processed!</h3>";
    echo "<p>Total rows read: " . count($parsedData) . "</p>";

    // Output the data nicely for testing purposes
    echo "<pre>";
    print_r($parsedData);
    echo "</pre>";

  } catch (Exception $e) {
    echo "<h3 style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
  }

  echo "<p style='color: green;'>Success! <strong>{$fileName}</strong> was automatically uploaded and received.</p>";
} else {
  echo "<p style='color: red;'>Upload failed with error code: {$fileError}</p>";
}
?>