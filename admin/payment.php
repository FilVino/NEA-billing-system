<?php
include '../php/sessionVerify.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Payment | NEA Billing System</title>

  <link rel="stylesheet" href="../src/payment.css" />
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
            <div class="form-icon">💳</div>
          </div>
          <h1>Add Payment</h1>
          <p class="form-subtitle">Record customer payment transactions</p>
        </div>

        <form class="payment-form" action="" method="post" id="paymentForm">
          <!-- Bill ID -->
          <div class="input-group">
            <input type="text" name="bid" id="bid" required placeholder=" " autocomplete="off" />
            <label for="bid">Bill ID</label>
            <span class="input-hint">Enter the Bill ID for which payment is being made</span>
          </div>

          <!-- Payment Date -->
          <div class="input-group">
            <input type="date" name="pdate" id="pdate" required placeholder=" " />
            <label for="pdate">Payment Date</label>
            <span class="input-hint">Date when the payment was received</span>
          </div>

          <!-- Payment Amount -->
          <div class="input-group">
            <input type="number" name="pamount" id="pamount" required placeholder=" " step="0.01" min="0" autocomplete="off" />
            <label for="pamount">Payment Amount (Rs.)</label>
            <span class="input-hint">Amount paid by the customer</span>
          </div>

          <!-- Payment Type ID -->
          <div class="input-group">
            <input type="text" name="paymentTypeId" id="paymentTypeId" required placeholder=" " autocomplete="off" />
            <label for="paymentTypeId">Payment Type ID</label>
            <span class="input-hint">Reference to payment method (Cash, Bank Transfer, etc.)</span>
          </div>

          <!-- Rebate Amount -->
          <div class="input-group">
            <input type="number" name="rebeatAmount" id="rebeatAmount" placeholder=" " step="0.01" min="0" value="0" autocomplete="off" />
            <label for="rebeatAmount">Rebate Amount (Rs.)</label>
            <span class="input-hint">Discount or rebate applied (if any)</span>
          </div>

          <!-- Fine Amount -->
          <div class="input-group">
            <input type="number" name="fineAmount" id="fineAmount" placeholder=" " step="0.01" min="0" value="0" autocomplete="off" />
            <label for="fineAmount">Fine Amount (Rs.)</label>
            <span class="input-hint">Late payment penalty (if any)</span>
          </div>

          <!-- Net Payment Preview -->
          <div class="payment-preview" id="paymentPreview" style="display: none;">
            <div class="preview-row">
              <span class="preview-label">Payment Amount:</span>
              <span class="preview-value" id="previewAmount">0</span>
            </div>
            <div class="preview-row">
              <span class="preview-label">Rebate:</span>
              <span class="preview-value negative" id="previewRebate">0</span>
            </div>
            <div class="preview-row">
              <span class="preview-label">Fine:</span>
              <span class="preview-value positive" id="previewFine">0</span>
            </div>
            <div class="preview-row total">
              <span class="preview-label">Net Amount:</span>
              <span class="preview-value total-amount" id="previewTotal">0</span>
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
              Record Payment
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
    $bid = e($_POST['bid']);
    $pdate = e($_POST['pdate']);
    $pamount = e($_POST['pamount']);
    $paymentTypeId = e($_POST['paymentTypeId']);
    $rebeatAmount = e($_POST['rebeatAmount']);
    $fineAmount = e($_POST['fineAmount']);

    // Validate inputs
    $errors = [];
    if (empty($bid)) $errors[] = "Bill ID is required";
    if (empty($pdate)) $errors[] = "Payment date is required";
    if (empty($pamount) || $pamount <= 0) $errors[] = "Valid payment amount is required";
    if (empty($paymentTypeId)) $errors[] = "Payment type ID is required";
    
    // Set default values for optional fields
    $rebeatAmount = $rebeatAmount ?: 0;
    $fineAmount = $fineAmount ?: 0;

    if (empty($errors)) {
      $apiResponse = callApi('/payment', [
        'BID' => $bid,
        'PDate' => $pdate,
        'PAmount' => $pamount,
        'Payment_Option_Id' => $paymentTypeId,
        'Rebeat_Amt' => $rebeatAmount,
        'Fine_Amt' => $fineAmount
      ]);

      if ($apiResponse['success']) {
        echo '<script>alert("✓ Payment recorded successfully!")</script>';
        echo '<script>window.location.href = "payment.php";</script>';
      } else {
        $message = htmlspecialchars($apiResponse['message'] ?? 'Failed to record payment');
        echo '<script>alert("✗ Error: ' . $message . '")</script>';
      }
    } else {
      echo '<script>alert("✗ Error: ' . implode("\\n", $errors) . '")</script>';
    }
  }
  ?>

  <script>
    // Live payment calculation
    const paymentAmount = document.getElementById('pamount');
    const rebateAmount = document.getElementById('rebeatAmount');
    const fineAmount = document.getElementById('fineAmount');
    const paymentPreview = document.getElementById('paymentPreview');
    const previewAmount = document.getElementById('previewAmount');
    const previewRebate = document.getElementById('previewRebate');
    const previewFine = document.getElementById('previewFine');
    const previewTotal = document.getElementById('previewTotal');

    function updatePaymentPreview() {
      const amount = parseFloat(paymentAmount.value) || 0;
      const rebate = parseFloat(rebateAmount.value) || 0;
      const fine = parseFloat(fineAmount.value) || 0;
      const netTotal = amount - rebate + fine;
      
      if (amount > 0) {
        paymentPreview.style.display = 'block';
        previewAmount.textContent = 'Rs. ' + amount.toFixed(2);
        previewRebate.textContent = '- Rs. ' + rebate.toFixed(2);
        previewFine.textContent = '+ Rs. ' + fine.toFixed(2);
        previewTotal.textContent = 'Rs. ' + netTotal.toFixed(2);
      } else {
        paymentPreview.style.display = 'none';
      }
    }

    paymentAmount.addEventListener('input', updatePaymentPreview);
    rebateAmount.addEventListener('input', updatePaymentPreview);
    fineAmount.addEventListener('input', updatePaymentPreview);
  </script>
</body>

</html>