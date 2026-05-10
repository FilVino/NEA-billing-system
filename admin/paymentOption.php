<?php
include '../php/sessionVerify.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Payment Option | NEA Billing System</title>

  <link rel="stylesheet" href="../src/paymentOption.css" />
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
            <div class="form-icon">🏦</div>
          </div>
          <h1>Add Payment Option</h1>
          <p class="form-subtitle">Register available payment methods for customer transactions</p>
        </div>

        <form class="paymentoption-form" action="" method="post" id="paymentOptionForm">
          <!-- Payment Option ID -->
          <div class="input-group">
            <input type="text" name="poid" id="poid" required placeholder=" " autocomplete="off" />
            <label for="poid">Payment Option ID</label>
            <span class="input-hint">Unique identifier for the payment method</span>
          </div>

          <!-- Payment Method Name -->
          <div class="input-group">
            <input type="text" name="paymentMethod" id="paymentMethod" required placeholder=" " autocomplete="off" />
            <label for="paymentMethod">Payment Method</label>
            <span class="input-hint">Examples: eSewa, Khalti, FonePay, Bank Transfer, Cash</span>
          </div>

          <!-- Status Toggle -->
          <div class="toggle-group">
            <label class="toggle-label">Payment Method Status</label>
            <div class="toggle-switch">
              <input type="checkbox" name="currStatus" id="currStatus" value="1" checked />
              <label for="currStatus" class="toggle-slider"></label>
              <span class="toggle-text-active">Active</span>
              <span class="toggle-text-inactive">Inactive</span>
            </div>
            <span class="input-hint">Active payment methods will be available to customers</span>
          </div>

          <!-- Popular Payment Methods Suggestions -->
          <div class="suggestions-group">
            <label class="suggestions-label">Popular Payment Methods:</label>
            <div class="suggestions-buttons">
              <button type="button" class="suggestion-btn" onclick="setPaymentMethod('eSewa')">eSewa</button>
              <button type="button" class="suggestion-btn" onclick="setPaymentMethod('Khalti')">Khalti</button>
              <button type="button" class="suggestion-btn" onclick="setPaymentMethod('FonePay')">FonePay</button>
              <button type="button" class="suggestion-btn" onclick="setPaymentMethod('Bank Transfer')">Bank Transfer</button>
              <button type="button" class="suggestion-btn" onclick="setPaymentMethod('Cash')">Cash</button>
              <button type="button" class="suggestion-btn" onclick="setPaymentMethod('Mobile Banking')">Mobile Banking</button>
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
              Add Payment Option
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
    $poid = e($_POST['poid']);
    $paymentMethod = e($_POST['paymentMethod']);
    $currStatus = isset($_POST['currStatus']) ? 1 : 0;

    // Validate inputs
    $errors = [];
    if (empty($poid)) $errors[] = "Payment Option ID is required";
    if (empty($paymentMethod)) $errors[] = "Payment method name is required";

    if (empty($errors)) {
      $apiResponse = callApi('/payment-option', [
        'POID' => $poid,
        'paymentMethod' => $paymentMethod,
        'currStatus' => $currStatus
      ]);

      if ($apiResponse['success']) {
        echo '<script>alert("✓ Payment option added successfully!")</script>';
        echo '<script>window.location.href = "paymentOption.php";</script>';
      } else {
        $message = htmlspecialchars($apiResponse['message'] ?? 'Failed to add payment option');
        echo '<script>alert("✗ Error: ' . $message . '")</script>';
      }
    } else {
      echo '<script>alert("✗ Error: ' . implode("\\n", $errors) . '")</script>';
    }
  }
  ?>

  <script>
    // Function to auto-fill payment method from suggestions
    function setPaymentMethod(method) {
      document.getElementById('paymentMethod').value = method;
      document.getElementById('paymentMethod').focus();
      
      // Trigger floating label effect
      const input = document.getElementById('paymentMethod');
      input.dispatchEvent(new Event('input'));
      
      // Auto-generate POID based on method name
      const poidInput = document.getElementById('poid');
      if (!poidInput.value) {
        // Generate POID from method name (lowercase, no spaces)
        let generatedId = method.toLowerCase().replace(/\s+/g, '_');
        poidInput.value = generatedId;
        poidInput.dispatchEvent(new Event('input'));
      }
    }

    // Auto-generate POID when payment method is entered (optional)
    const paymentMethodInput = document.getElementById('paymentMethod');
    const poidInput = document.getElementById('poid');
    
    paymentMethodInput.addEventListener('blur', function() {
      if (!poidInput.value && this.value) {
        let generatedId = this.value.toLowerCase().replace(/\s+/g, '_');
        poidInput.value = generatedId;
        poidInput.dispatchEvent(new Event('input'));
      }
    });
  </script>
</body>

</html>