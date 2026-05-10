<?php
include '../php/sessionVerify.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Demand Rate | NEA Billing System</title>

    <link rel="stylesheet" href="../src/demandRate.css" />
</head>

<?php
include("../php/dbconnect.php");

$demandQuery = "SELECT demand_type_id, descrip FROM demandtype WHERE currStatus = 1";
$demandResult = mysqli_query($conn, $demandQuery);
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
                        <div class="form-icon">💰</div>
                    </div>
                    <h1>Add Demand Rate</h1>
                    <p class="form-subtitle">Set pricing rates for different electricity demand types</p>
                </div>

                <form class="demandrate-form" action="" method="post" id="demandRateForm">
                    <!-- Demand Rate Amount -->
                    <div class="input-group">
                        <input type="number" name="demand-rate" id="demandRate" required placeholder=" " step="0.01" min="0" autocomplete="off" />
                        <label for="demandRate">Demand Rate (Rs. per unit)</label>
                        <span class="input-hint">Enter the rate per unit in Nepalese Rupees</span>
                    </div>

                    <!-- Effective Date -->
                    <div class="input-group">
                        <input type="date" name="effective-date" id="effectiveDate" required placeholder=" " />
                        <label for="effectiveDate">Effective Date</label>
                        <span class="input-hint">Date from which this rate becomes applicable</span>
                    </div>

                    <!-- Demand Type Selection -->
                    <div class="select-group">
                        <label for="demandType" class="select-label">Demand Type</label>
                        <div class="select-wrapper">
                            <select name="demand-type" id="demandType" required>
                                <option value="" disabled selected>Select demand type</option>
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

                    <!-- Status Toggle -->
                    <div class="toggle-group">
                        <label class="toggle-label">Rate Status</label>
                        <div class="toggle-switch">
                            <input type="checkbox" name="currStatus" id="currStatus" value="1" checked />
                            <label for="currStatus" class="toggle-slider"></label>
                            <span class="toggle-text-active">Active (Current Rate)</span>
                            <span class="toggle-text-inactive">Inactive</span>
                        </div>
                        <span class="input-hint">Set as current rate if this is the active pricing</span>
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
                            Add Demand Rate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <p>
            Copyright © <span id="year-change"></span> Nepal Electricity Authority ❘
            All Rights Reserved.
        </p>
    </footer>

    <?php
    if (isset($_POST['submit'])) {
        $demandRate = mysqli_real_escape_string($conn, $_POST['demand-rate']);
        $effectiveDate = mysqli_real_escape_string($conn, $_POST['effective-date']);
        $currStatus = isset($_POST['currStatus']) ? 1 : 0;
        $demandTypeID = mysqli_real_escape_string($conn, $_POST['demand-type']);

        // Validate inputs
        $errors = [];
        if (empty($demandRate) || $demandRate <= 0) $errors[] = "Valid demand rate is required";
        if (empty($effectiveDate)) $errors[] = "Effective date is required";
        if (empty($demandTypeID)) $errors[] = "Demand type is required";

        if (empty($errors)) {
            // If this rate is set as current, deactivate other rates for this demand type
            if ($currStatus == 1) {
                $deactivateSql = "UPDATE demandrate SET is_current = 0 WHERE demand_type_id = '$demandTypeID'";
                $conn->query($deactivateSql);
            }

            $sql = "INSERT INTO `demandrate` (demand_rate, effective_rate, is_current, demand_type_id) 
                    VALUES ('$demandRate', '$effectiveDate', '$currStatus', '$demandTypeID')";

            if ($conn->query($sql) === true) {
                echo '<script>alert("✓ Demand rate added successfully!")</script>';
                echo '<script>window.location.href = "demandRate.php";</script>';
            } else {
                echo '<script>alert("✗ Error: Failed to add demand rate. ' . mysqli_error($conn) . '")</script>';
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