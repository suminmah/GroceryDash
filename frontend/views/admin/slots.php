<?php
// frontend/views/admin/slots.php
/**
 * @var array $slots Provided by AdminController::slotsList()
 * @var string|null $error
 * @var string|null $success
 */
// Handled via AdminController to match layout context parameters safely
?>

<div class="p-4 w-100">
    <div class="admin-header" style="margin-bottom: 2rem;">
        <h1>Delivery Slots Management</h1>
        <p style="color: #666;">Create, schedule, and manage user delivery windows and capacity thresholds for GroceryDash.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="flash flash-error" style="background:#fef2f2; color:#b91c1c; padding:1rem; border-radius:6px; margin-bottom:1.5rem; border: 1px solid #fca5a5;">
            <i class="bi bi-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="flash flash-success" style="background:#f0fdf4; color:#15803d; padding:1rem; border-radius:6px; margin-bottom:1.5rem; border: 1px solid #bbf7d0;">
            <i class="bi bi-check-circle me-2"></i> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 rounded-4 p-4 mb-4" style="background:#fff; border-radius:12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); max-width:900px;">
        <h5 class="text-dark fw-bold mb-3" style="font-size: 1.1rem;">Create New Delivery Slot</h5>
        
        <form method="POST" action="<?= APP_URL ?>/admin/slots">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted small text-uppercase">Slot Date</label>
                    <input type="date" name="slot_date" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted small text-uppercase">Start Time</label>
                    <input type="time" name="start_time" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted small text-uppercase">End Time</label>
                    <input type="time" name="end_time" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted small text-uppercase">Max Capacity</label>
                    <input type="number" name="max_capacity" class="form-control" placeholder="e.g. 15" min="1" required>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-success px-4 py-2" style="background:#2c7a4d; border:none; border-radius:6px; font-weight:500;">
                    <i class="bi bi-plus-circle me-1"></i> Add Slot
                </button>
            </div>
        </form>
    </div>

    <div class="card shadow-sm border-0 rounded-4 p-0" style="background:#fff; border-radius:12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); max-width:900px; overflow: hidden;">
        <div class="p-4 border-bottom bg-light d-flex justify-content-between align-items-center">
            <h5 class="text-dark fw-bold mb-0" style="font-size: 1.1rem;">Active Scheduled Slots</h5>
            <span class="badge bg-secondary rounded-pill px-3 py-2 fw-medium text-uppercase" style="font-size:0.75rem;">Total: <?= count($slots ?? []) ?></span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 600px;">
                <thead class="table-light text-uppercase fs-7" style="font-size: 0.75rem; letter-spacing: 0.05em; color: #6b7280;">
                    <tr>
                        <th class="ps-4 py-3">Scheduled Date</th>
                        <th class="py-3">Time Range Window</th>
                        <th class="py-3">Booked / Max Capacity</th>
                        <th class="pe-4 py-3 text-end">Action Options</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($slots)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted fs-6">
                            <i class="bi bi-calendar-x d-block mb-2 text-secondary" style="font-size: 2rem;"></i>
                            No delivery slots found. Fill out the form above to build schedules.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($slots as $slot): ?>
                        <tr style="transition: background 0.2s ease;">
                            <td class="ps-4 fw-medium text-dark">
                                <i class="bi bi-calendar3 me-2 text-muted"></i>
                                <?= date('M d, Y', strtotime($slot['slot_date'])) ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-3 py-2 font-monospace">
                                    <?= date('h:i A', strtotime($slot['start_time'])) ?> - <?= date('h:i A', strtotime($slot['end_time'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px; max-width: 100px; border-radius: 4px;">
                                        <?php 
                                            $booked = (int)($slot['booked'] ?? 0);
                                            $capacity = (int)($slot['capacity'] ?? 1);
                                            $percentage = min(100, ($booked / $capacity) * 100);
                                            $barColor = $percentage >= 90 ? 'bg-danger' : ($percentage >= 50 ? 'bg-warning' : 'bg-success');
                                        ?>
                                        <div class="progress-bar <?= $barColor ?>" style="width: <?= $percentage ?>%"></div>
                                    </div>
                                    <span class="fw-semibold" style="font-size:0.9rem;">
                                        <?= $booked ?> <span class="text-muted fw-normal">/ <?= $capacity ?></span>
                                    </span>
                                </div>
                            </td>
                            <td class="pe-4 text-end">
                                <form method="POST" action="<?= APP_URL ?>/admin/slots/<?= $slot['slot_id'] ?>/delete" onsubmit="return confirm('Are you sure you want to delete this delivery slot?');" style="display:inline-block;">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-3 d-inline-flex align-items-center gap-1" style="padding: 0.35rem 0.75rem;">
                                        <i class="bi bi-trash3" style="font-size: 0.85rem;"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>