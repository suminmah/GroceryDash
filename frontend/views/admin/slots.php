<?php $pageTitle = 'Delivery Slots'; 
require __DIR__ . '/../layouts/header.php'; 
?>

<h1>Delivery Slots</h1>
<form method="POST" action="<?= APP_URL ?>/admin/slots">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <input type="date" name="slot_date" required> 
    <input type="time" name="start_time" required> 
    <input type="time" name="end_time" required>
    <input type="number" name="max_capacity" placeholder="Capacity">
    <button type="submit">Add Slot</button>
</form>
<table class="admin-table">
    <thead><tr><th>Date</th><th>Time</th><th>Booked/Capacity</th><th>Delete</th></tr></thead>
    <tbody>
    <?php foreach ($slots ?? [] as $slot): ?>
        <tr>
            <td><?= $slot['slot_date'] ?></td>
            <td><?= $slot['start_time'] ?> - <?= $slot['end_time'] ?></td>
            <td><?= $slot['booked'] ?? 0 ?>/<?= $slot['capacity'] ?></td>
            <td>
                <form method="POST" action="<?= APP_URL ?>/admin/slots/<?= $slot['slot_id'] ?>/delete">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <button type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../layouts/footer.php'; ?>