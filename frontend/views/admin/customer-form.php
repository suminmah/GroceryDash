<?php $pageTitle = 'Create Separated Account'; ?>

<div class="admin-container" style="max-width: 900px; margin: 0 auto; padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Add New Customers</h2>
        <a href="<?= APP_URL ?>/admin/customers" style="text-decoration: none; color: #4b5563;">⬅️ Back</a>
    </div>

    <?php if (!empty($error)): ?>
        <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            ⚠️ <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form action="<?= APP_URL ?>/admin/customers" method="POST" style="background: white; padding: 25px; border: 1px solid #e5e7eb; border-radius: 8px; display: flex; flex-direction: column; gap: 20px;">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <div style="border-bottom: 1px solid #f3f4f6; padding-bottom: 10px; font-weight: bold; color: #1f2937;">Customer Details</div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div>
                <label style="display:block; margin-bottom:5px; font-size:13px; font-weight:600;">Customer Full Name</label>
                <input type="text" name="name" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;" placeholder="e.g. Arun Thapa" required>
            </div>
            <div>
                <label style="display:block; margin-bottom:5px; font-size:13px; font-weight:600;">Mobile Number</label>
                <input type="text" name="phone" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;" placeholder="e.g. +977 98XXXXXXXX">
            </div>
        </div>

        <div style="border-bottom: 1px solid #f3f4f6; padding-bottom: 10px; font-weight: bold; color: #1f2937;">Account Credentials</div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div>
                <label style="display:block; margin-bottom:5px; font-size:13px; font-weight:600;">Login Email</label>
                <input type="email" name="email" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;" placeholder="arun@grocerydash.com" required>
            </div>
            <div>
                <label style="display:block; margin-bottom:5px; font-size:13px; font-weight:600;">Password</label>
                <input type="password" name="password" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;" placeholder="••••••••" required>
            </div>
        </div>

        <div>
            <label style="display:block; margin-bottom:5px; font-size:13px; font-weight:600;">Access Role</label>
            <select name="role" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px; background: white;">
                <option value="customer" selected>Consumer Client User (Customer)</option>
                <option value="admin">System Operations Operator (Admin)</option>
            </select>
        </div>

        <div style="text-align: right; margin-top: 10px;">
            <button type="submit" style="background: #10b981; color: white; border: 0; padding: 10px 24px; border-radius: 4px; font-weight: 600; cursor: pointer;">
                Submit
            </button>
        </div>
    </form>
</div>