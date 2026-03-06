<?php
/**
 * User Management Page
 *
 * Displays user management interface with data table
 * Data is pre-rendered on server using PHP
 */

include __DIR__ . '/services/mock_education_service.php';
use App\Services\MockEducationService;

$mockService = new MockEducationService();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon" />
  <title>Cao Thang CNTT Dashboard</title>

  <!-- ========== All CSS files linkup ========= -->
  <link rel="stylesheet" href="public/css/base.css" />
  <link rel="stylesheet" href="public/css/common.css" />
  <link rel="stylesheet" href="public/css/dashboard.css" />
</head>

<body>
  <!-- ======== Preloader =========== -->
  <?php include_once 'includes/preloader.php'; ?>
  <!-- ======== Preloader End =========== -->

  <!-- ======== Dashboard Sidebar =========== -->
  <?php include_once 'includes/dashboard_sidebar.php'; ?>
  <!-- ======== Dashboard Sidebar End =========== -->

  <!-- ======== main-wrapper start =========== -->
  <main class="main-wrapper">
    <!-- ========== Dashboard Header ========== -->
    <?php include_once 'includes/dashboard_header.php'; ?>
    <!-- ========== Dashboard Header End ========== -->

    <!-- ========== main content start ========== -->
    <section class="section">
      <div class="container container-fluid">
        <!-- ========== title-wrapper start ========== -->
        <div class="title-wrapper">
          <div class="flex justify-between items-center">
            <div class="col-6 col-md-6">
              <h2 class="title text-2xl font-semibold">Users</h2>
            </div>
            <div class="col-6 col-md-6">
            </div>
          </div>
        </div>
        <!-- ========== title-wrapper end ========== -->

        <!-- ========== table-wrapper start ========== -->
        <div class="table-wrapper shadow rounded-md">
          <table class="data-table">
            <thead>
              <tr>
                <th>
                  <!-- Index Dummy Placeholder -->
                </th>
                <th>
                  <h6>Name</h6>
                </th>
                <th>
                  <h6>Email</h6>
                </th>
                <th>
                  <h6>Gender</h6>
                </th>
                <th>
                  <h6>Date of Birth</h6>
                </th>
                <th>
                  <h6>Phone</h6>
                </th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($mockService->getAllStudents() as $index => $user): ?>
                <tr onclick="window.location.href='user_detail?id=<?php echo $user->student_id; ?>';">
                  <td class="data-table__id">
                    #<?php echo $index + 1; ?>
                  </td>
                  <td>
                    <?php echo $user->fullname ?? 'N/A'; ?>
                  </td>
                  <td>
                    <?php echo $user->account->email ?? 'N/A'; ?>
                  </td>
                  <td>
                    <?php echo $user->gender ?? 'N/A'; ?>
                  </td>
                  <td>
                    <?php echo $user->dob ?? 'N/A'; ?>
                  </td>
                  <td>
                    <?php echo $user->phone ?? 'N/A'; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <!-- ========== table-wrapper end ========== -->
      </div>
    </section>
    <!-- ========== main content end ========== -->

    <!-- ========== Dashboard Footer =========== -->
    <?php include_once 'includes/dashboard_footer.php'; ?>
    <!-- ========== Dashboard Footer End =========== -->
  </main>
  <!-- ======== main-wrapper end =========== -->

  <!-- ========= Dashboard Scripts ======== -->
  <?php include_once 'includes/dashboard_scripts.php'; ?>
  <!-- ========= Dashboard Scripts End ======== -->
</body>

</html>