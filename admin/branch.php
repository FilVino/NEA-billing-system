<?php
include '../php/sessionVerify.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Branch | NEA Billing System</title>

  <link rel="stylesheet" href="../src/branch.css" />
</head>

<body>
  <?php
  $currentPage = basename($_SERVER['PHP_SELF']);
  include '../components/navbar.php';
  ?>

  <main class="main-content">
    <div class="page-container">
      <!-- Back Navigation -->
      <div class="back-nav">
        <a href="./home.php" class="back-button">
          <svg class="back-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
          </svg>
          Back to Dashboard
        </a>
      </div>

      <!-- Main Form Card -->
      <div class="form-card">
        <div class="form-header">
          <div class="form-icon-wrapper">
            <div class="form-icon">🏢</div>
          </div>
          <h1>Add New Branch</h1>
          <p class="form-subtitle">Register a new branch location for the billing system</p>
        </div>

        <form class="branch-form" action="" method="post" id="branchForm">
          <!-- Branch Name -->
          <div class="input-group">
            <input type="text" name="bname" id="bname" required placeholder=" " autocomplete="off" />
            <label for="bname">Branch Name</label>
          </div>

          <!-- Status Toggle -->
          <div class="toggle-group">
            <label class="toggle-label">Branch Status</label>
            <div class="toggle-switch">
              <input type="checkbox" name="status" id="status" value="1" checked />
              <label for="status" class="toggle-slider"></label>
              <span class="toggle-text-active">Active</span>
              <span class="toggle-text-inactive">Inactive</span>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="form-actions">
            <button type="reset" class="btn-reset">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
              Reset
            </button>
            <button type="submit" name="submit" class="btn-submit">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"/>
                <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"/>
              </svg>
              Add Branch
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>

  <?php include '../components/footer.php'; ?>

  <?php
  if (isset($_POST['submit'])) {
    $bname = e($_POST['bname']);
    $status = isset($_POST['status']) ? 1 : 0;

    if (empty($bname)) {
      echo '<script>alert("✗ Error: Branch name is required!")</script>';
    } else {
      $apiResponse = callApi('/branch', [
        'branch_name' => $bname,
        'currStatus' => $status
      ]);

      if ($apiResponse['success']) {
        echo '<script>alert("✓ Branch added successfully!")</script>';
        echo '<script>window.location.href = "branch.php";</script>';
      } else {
        $message = htmlspecialchars($apiResponse['message'] ?? 'Failed to add branch');
        echo '<script>alert("✗ Error: ' . $message . '")</script>';
      }
    }
  }
  ?>
</body>

</html>