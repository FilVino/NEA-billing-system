<?php
include '../php/sessionVerify.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Demand Type | NEA Billing System</title>

  <link rel="stylesheet" href="../src/demand.css" />
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
            <div class="form-icon">⚡</div>
          </div>
          <h1>Add Demand Type</h1>
          <p class="form-subtitle">Configure electricity demand categories for billing</p>
        </div>

        <form class="demand-form" action="" method="post" id="demandForm">
          <!-- Demand Type ID -->
          <div class="input-group">
            <input type="text" name="demandTypeID" id="demandTypeID" required placeholder=" " autocomplete="off" />
            <label for="demandTypeID">Demand Type Code</label>
            <span class="input-hint">Example: 5AMP, 10AMP, 15AMP, Commercial, Industrial</span>
          </div>

          <!-- Demand Description -->
          <div class="input-group">
            <input type="text" name="demandDesc" id="demandDesc" required placeholder=" " autocomplete="off" />
            <label for="demandDesc">Demand Description</label>
            <span class="input-hint">Full description of the demand type</span>
          </div>

          <!-- Status Toggle -->
          <div class="toggle-group">
            <label class="toggle-label">Demand Type Status</label>
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
              Add Demand Type
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>

  <?php include '../components/footer.php'; ?>

  <?php
  include("../php/dbconnect.php");

  if (isset($_POST['submit'])) {
    $demandTypeID = mysqli_real_escape_string($conn, $_POST['demandTypeID']);
    $demandDesc = mysqli_real_escape_string($conn, $_POST['demandDesc']);
    $status = isset($_POST['status']) ? 1 : 0;

    // Validate inputs
    $errors = [];
    if (empty($demandTypeID)) $errors[] = "Demand type code is required";
    if (empty($demandDesc)) $errors[] = "Demand description is required";

    if (empty($errors)) {
      // Check if demand type already exists
      $checkSql = "SELECT demand_type_id FROM demandtype WHERE demand_type_id = '$demandTypeID' OR descrip = '$demandDesc'";
      $checkResult = $conn->query($checkSql);
      
      if ($checkResult->num_rows > 0) {
        echo '<script>alert("✗ Error: Demand type code or description already exists!")</script>';
      } else {
        $sql = "INSERT INTO `demandtype` (demand_type_id, descrip, currStatus) 
                VALUES ('$demandTypeID', '$demandDesc', '$status')";

        if ($conn->query($sql) === true) {
          echo '<script>alert("✓ Demand type added successfully!")</script>';
          echo '<script>window.location.href = "demand.php";</script>';
        } else {
          echo '<script>alert("✗ Error: Failed to add demand type. ' . mysqli_error($conn) . '")</script>';
        }
      }
    } else {
      echo '<script>alert("✗ Error: ' . implode("\\n", $errors) . '")</script>';
    }
    $conn->close();
  }
  ?>

  <script src="../src/index.js"></script>
</body>

</html>