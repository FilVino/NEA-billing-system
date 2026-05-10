<?php
include '../php/sessionVerify.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Customer | NEA Billing System</title>

  <link rel="stylesheet" href="../src/customer.css" />
</head>

<!-- db connect -->
<?php
include("../php/dbconnect.php"); //DB connection
$branchQuery = "SELECT branch_id, branch_name FROM branch";
$branchResult = $conn->query($branchQuery);

$demandQuery = "SELECT demand_type_id, descrip FROM demandtype";
$demandResult = $conn->query($demandQuery);
?>

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
            <div class="form-icon">👤</div>
          </div>
          <h1>Add New Customer</h1>
          <p class="form-subtitle">Register a new customer for the electrical billing system</p>
        </div>

        <form class="customer-form" action="" method="post" id="customerForm">
          <!-- SC NO -->
          <div class="input-group">
            <input type="text" name="scno" id="scno" required placeholder=" " autocomplete="off" />
            <label for="scno">SC Number</label>
          </div>

          <!-- Full Name -->
          <div class="input-group">
            <input type="text" name="fname" id="fname" required placeholder=" " autocomplete="off" />
            <label for="fname">Full Name</label>
          </div>

          <!-- Address -->
          <div class="input-group">
            <input type="text" name="address" id="address" required placeholder=" " autocomplete="off" />
            <label for="address">Address</label>
          </div>

          <!-- Mobile No -->
          <div class="input-group">
            <input type="tel" name="mobno" id="mobno" required placeholder=" " pattern="[0-9]{10}" title="Please enter a valid 10-digit mobile number" autocomplete="off" />
            <label for="mobno">Mobile Number</label>
          </div>

          <!-- Branch Selection -->
          <div class="select-group">
            <label for="branchID" class="select-label">Select Branch</label>
            <div class="select-wrapper">
              <select name="branchID" id="branchID" required>
                <option value="" disabled selected>Choose a branch</option>
                <?php
                while ($row = $branchResult->fetch_assoc()) {
                  echo "<option value='" . $row['branch_id'] . "'>" . htmlspecialchars($row['branch_name']) . "</option>";
                }
                ?>
              </select>
              <svg class="select-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9"></polyline>
              </svg>
            </div>
          </div>

          <!-- Demand Type Selection -->
          <div class="select-group">
            <label for="demandTypeID" class="select-label">Demand Type</label>
            <div class="select-wrapper">
              <select name="demandTypeID" id="demandTypeID" required>
                <option value="" disabled selected>Choose demand type</option>
                <?php
                while ($row = $demandResult->fetch_assoc()) {
                  echo "<option value='" . $row['demand_type_id'] . "'>" . htmlspecialchars($row['descrip']) . "</option>";
                }
                ?>
              </select>
              <svg class="select-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9"></polyline>
              </svg>
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
              Add Customer
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>

  <?php include '../components/footer.php'; ?>

  <?php
  if (isset($_POST['submit'])) {
    $scnd = mysqli_real_escape_string($conn, $_POST['scno']);
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $mobno = mysqli_real_escape_string($conn, $_POST['mobno']);
    $branchID = mysqli_real_escape_string($conn, $_POST['branchID']);
    $demandTypeID = mysqli_real_escape_string($conn, $_POST['demandTypeID']);

    // Validate inputs
    $errors = [];
    if (empty($scnd)) $errors[] = "SC Number is required";
    if (empty($fname)) $errors[] = "Full name is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($mobno)) $errors[] = "Mobile number is required";
    if (!preg_match("/^[0-9]{10}$/", $mobno)) $errors[] = "Mobile number must be 10 digits";

    if (empty($errors)) {
      $sql = "INSERT INTO `customer` (SCND, Fullname, AddressName, MobileNo, BranchId, demand_type_id) 
              VALUES ('$scnd', '$fname', '$address', '$mobno', '$branchID', '$demandTypeID')";

      if ($conn->query($sql) === true) {
        echo '<script>alert("✓ Customer added successfully!")</script>';
        echo '<script>window.location.href = "customer.php";</script>';
      } else {
        echo '<script>alert("✗ Error: Failed to add customer. ' . mysqli_error($conn) . '")</script>';
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