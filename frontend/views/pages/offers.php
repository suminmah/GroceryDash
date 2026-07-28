<?php $pageTitle = 'Exclusive Offers'; require __DIR__ . '/../layouts/header.php'; ?>

<style>
/* Base overrides */
.offers-page {
    font-family: 'Inter', 'Roboto', sans-serif;
    background-color: #f8fafc;
    min-height: calc(100vh - 80px);
    padding: 60px 20px;
}
.offers-header {
    text-align: center;
    margin-bottom: 60px;
}
.offers-header h1 {
    font-size: 3.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 15px;
    letter-spacing: -1px;
}
.offers-header p {
    font-size: 1.25rem;
    color: #64748b;
    max-width: 600px;
    margin: 0 auto;
}

/* Premium Offer Card */
.offer-showcase {
    max-width: 1100px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0,0,0,0.02);
    display: flex;
    flex-direction: row;
    position: relative;
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease;
}
.offer-showcase:hover {
    transform: translateY(-8px);
    box-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0,0,0,0.02);
}

/* Image Side */
.offer-image-wrap {
    flex: 1.2;
    position: relative;
    overflow: hidden;
    min-height: 400px;
}
.offer-image-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.8s ease;
}
.offer-showcase:hover .offer-image-wrap img {
    transform: scale(1.05);
}

/* Floating Badge (Glassmorphism) */
.offer-badge {
    position: absolute;
    top: 24px;
    left: 24px;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    color: #ef4444;
    font-weight: 700;
    padding: 8px 16px;
    border-radius: 100px;
    font-size: 0.9rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.5);
    animation: float 3s ease-in-out infinite;
    z-index: 10;
}

/* Content Side */
.offer-content {
    flex: 1;
    padding: 60px 50px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: linear-gradient(145deg, #ffffff, #fcfcfd);
}
.offer-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 20px;
    line-height: 1.1;
}
.offer-desc {
    font-size: 1.1rem;
    color: #475569;
    line-height: 1.6;
    margin-bottom: 35px;
}
.discount-box {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 40px;
    padding: 20px;
    background: #fff0f0;
    border-radius: 16px;
    border: 1px dashed #fca5a5;
}
.discount-amount {
    font-size: 2.5rem;
    font-weight: 900;
    color: #dc2626;
    line-height: 1;
}
.discount-text {
    font-size: 1rem;
    color: #991b1b;
    font-weight: 600;
    line-height: 1.3;
}
.claim-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 18px 40px;
    background: #0f172a;
    color: #ffffff;
    text-decoration: none;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 100px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.2);
}
.claim-btn:hover {
    background: #1e293b;
    transform: translateY(-2px);
    color: #ffffff;
    box-shadow: 0 15px 20px -3px rgba(15, 23, 42, 0.3);
}
.claim-btn i {
    margin-left: 10px;
    transition: transform 0.3s ease;
}
.claim-btn:hover i {
    transform: translateX(5px);
}

@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-8px); }
    100% { transform: translateY(0px); }
}

@media (max-width: 900px) {
    .offer-showcase {
        flex-direction: column;
    }
    .offer-image-wrap {
        min-height: 300px;
    }
    .offer-content {
        padding: 40px 30px;
    }
    .offers-header h1 {
        font-size: 2.5rem;
    }
}
</style>

<div class="offers-page">
    <div class="offers-header">
        <h1>Exclusive Deals</h1>
        <p>Unlock premium savings on the freshest seasonal arrivals. Hand-picked just for you.</p>
    </div>

    <div class="offer-showcase">
        <div class="offer-image-wrap">
            <span class="offer-badge">✨ Limited Time Only</span>
            <img src="<?= APP_URL ?>/assets/images/summer-sale.png" alt="Summer Fruits Splash">
        </div>
        <div class="offer-content">
            <h2 class="offer-title">The Grand Summer Tropical Splash</h2>
            <p class="offer-desc">Dive into the season with our ultra-premium selection of sun-ripened mangoes, crisp watermelons, and sweet pineapples. Experience the freshest harvest directly from the farm.</p>
            
            <div class="discount-box">
                <div class="discount-amount">30%</div>
                <div class="discount-text">OFF ENTIRE<br>SUMMER BASKET</div>
            </div>
            
            <div>
                <a href="<?= APP_URL ?>/shop" class="claim-btn">
                    Claim Offer Now <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>