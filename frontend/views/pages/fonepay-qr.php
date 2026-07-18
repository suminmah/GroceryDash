<?php require __DIR__ . '/../layouts/header.php'; ?>

<div style="max-width: 500px; margin: 40px auto; padding: 30px; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center;">
    <img src="<?= APP_URL ?>/assets/images/fonepay-logo.png" alt="Fonepay" style="height: 50px; margin-bottom: 20px;">
    <h2>Scan to Pay</h2>
    <p>Please scan the QR code below using your Fonepay enabled banking apps or wallets.</p>
    
    <div id="qrcode" style="display: flex; justify-content: center; margin: 30px 0; padding: 10px; background: white; border-radius: 8px;"></div>
    
    <div id="status-message" style="margin-top: 20px; font-weight: 600; color: #6c757d;">
        <span style="display:inline-block; animation: pulse 1.5s infinite;">⏳</span> Waiting for payment...
    </div>
</div>

<style>
@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Generate QR Code
    var qrMessage = <?= json_encode($qrMessage) ?>;
    new QRCode(document.getElementById("qrcode"), {
        text: qrMessage,
        width: 250,
        height: 250,
        colorDark : "#e20e0e",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.M
    });

    // 2. Poll the status API instead of using WebSockets for UAT stability
    var pollInterval = setInterval(function() {
        fetch("<?= APP_URL ?>/checkout/fonepay/check-status")
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    clearInterval(pollInterval);
                    document.getElementById("status-message").innerHTML = "✅ Payment Successful! Verifying...";
                    document.getElementById("status-message").style.color = "#198754";
                    
                    // Secure redirection to backend verification gateway
                    window.location.href = "<?= APP_URL ?>/checkout/fonepay/verify";
                }
            })
            .catch(error => {
                console.error("Polling error:", error);
            });
    }, 5000); // Check every 5 seconds
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
