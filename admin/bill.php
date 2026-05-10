<?php
include '../php/sessionVerify.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add New Bill | NEA Billing System</title>

  <link rel="stylesheet" href="../src/bill.css" />
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
          Back to    Dashboard
        </a>
      </div>

      <!-- Main Form Card -->
      <div class="form-card">
        <div class="form-header">
          <div class="form-icon-wrapper">
            <div class="form-icon">📄</div>
          </div>
          <h1>Add New Bill</h1>
          <p class="form-subtitle">Enter customer electricity bill details</p>
        </div>

        <form class="bill-form" action="" method="post" id="billForm">
          <!-- Bill Date -->
          <div class="input-group">
            <input type="date" name="bdate" id="bdate" required />
            <label for="bdate">Bill Date</label>
          </div>

          <!-- Bill Year & Month Row -->
          <div class="form-row">
            <div class="input-group">
              <select name="byear" id="byear" required>
                <option value="" disabled selected>Select Year</option>
                <option value="2080">2080</option>
                <option value="2079">2079</option>
                <option value="2078">2078</option>
                <option value="2077">2077</option>
                <option value="2076">2076</option>
                <option value="2075">2075</option>
                <option value="2074">2074</option>
                <option value="2073">2073</option>
                <option value="2072">2072</option>
                <option value="2071">2071</option>
                <option value="2070">2070</option>
                <option value="2069">2069</option>
              </select>
              <label for="byear"></label>
            </div>

            <div class="input-group">
              <select name="bmonth" id="bmonth" required>
                <option value="" disabled selected>Select Month</option>
                <option value="January">January</option>
                <option value="February">February</option>
                <option value="March">March</option>
                <option value="April">April</option>
                <option value="May">May</option>
                <option value="June">June</option>
                <option value="July">July</option>
                <option value="August">August</option>
                <option value="September">September</option>
                <option value="October">October</option>
                <option value="November">November</option>
                <option value="December">December</option>
              </select>
              <label for="bmonth"></label>
            </div>
          </div>

          <!-- Customer ID -->
          <div class="input-group">
            <input type="text" name="cusid" id="cusid" required placeholder=" " />
            <label for="cusid">Customer ID</label>
          </div>

          <!-- Reading Row -->
          <div class="form-row">
            <div class="input-group">
              <input type="number" name="currRead" id="currRead" step="1" required placeholder=" " />
              <label for="currRead">Current Reading (kWh)</label>
            </div>

            <div class="input-group">
              <input type="number" name="prevRead" id="prevRead" step="1" required placeholder=" " />
              <label for="prevRead">Previous Reading (kWh)</label>
            </div>
          </div>

          <!-- Consumption Preview -->
          <div class="consumption-preview" id="consumptionPreview" style="display: none;">
            <span class="preview-label">Consumption:</span>
            <span class="preview-value" id="consumptionValue">0</span>
            <span class="preview-unit">kWh</span>
          </div>

          <!-- Bill Amount -->
          <div class="input-group">
            <input type="number" name="bamount" id="bamount" step="0.01" required placeholder=" " />
            <label for="bamount">Bill Amount (Rs.)</label>
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
              Generate Bill
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>

  <?php include '../components/footer.php'; ?>

  <?php
  if (isset($_POST['submit'])) {
    $bdate = e($_POST['bdate']);
    $byear = e($_POST['byear']);
    $bmonth = e($_POST['bmonth']);
    $cusid = e($_POST['cusid']);
    $currRead = e($_POST['currRead']);
    $prevRead = e($_POST['prevRead']);
    $billAmount = e($_POST['bamount']);

    $apiResponse = callApi('/bill', [
      'BDate' => $bdate,
      'BYear' => $byear,
      'BMonth' => $bmonth,
      'CUSID' => $cusid,
      'Current_Reading' => $currRead,
      'Prev_Reading' => $prevRead,
      'Bamount' => $billAmount
    ]);

    if ($apiResponse['success']) {
      echo '<script>alert("✓ Bill added successfully!")</script>';
      echo '<script>window.location.href = "bill.php";</script>';
    } else {
      $message = htmlspecialchars($apiResponse['message'] ?? 'Failed to add bill. Please try again.');
      echo '<script>alert("✗ Error: ' . $message . '")</script>';
    }
  }
  ?>

  <script>
    // Live consumption preview
    const currReadInput = document.getElementById('currRead');
    const prevReadInput = document.getElementById('prevRead');
    const consumptionPreview = document.getElementById('consumptionPreview');
    const consumptionValue = document.getElementById('consumptionValue');

    function updateConsumption() {
      const curr = parseFloat(currReadInput.value) || 0;
      const prev = parseFloat(prevReadInput.value) || 0;
      const consumption = curr - prev;
      
      if (currReadInput.value && prevReadInput.value) {
        consumptionValue.textContent = consumption >= 0 ? consumption : 0;
        consumptionPreview.style.display = 'flex';
        if (consumption < 0) {
          consumptionPreview.style.background = '#fef2f0';
          consumptionPreview.style.borderLeftColor = '#e74c3c';
        } else {
          consumptionPreview.style.background = '#eef5f9';
          consumptionPreview.style.borderLeftColor = '#0C6D9E';
        }
      } else {
        consumptionPreview.style.display = 'none';
      }
    }

    currReadInput.addEventListener('input', updateConsumption);
    prevReadInput.addEventListener('input', updateConsumption);
  </script>
</body>

</html>