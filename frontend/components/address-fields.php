<?php // frontend/components/address-fields.php ?>
<div class="form-grid">
  <div class="form-group">
    <label for="line1">Address Line 1 *</label>
    <input type="text" id="line1" name="line1" class="input" placeholder="Street, house/flat no." required>
  </div>
  <div class="form-group">
    <label for="line2">Address Line 2</label>
    <input type="text" id="line2" name="line2" class="input" placeholder="Landmark, area (optional)">
  </div>
  <div class="form-group">
    <label for="city">City *</label>
    <input type="text" id="city" name="city" class="input" placeholder="City" required>
  </div>
  <div class="form-group">
    <label for="pincode">Pincode *</label>
    <input type="text" id="pincode" name="pincode" class="input" placeholder="Pincode" maxlength="10" required>
  </div>
</div>
