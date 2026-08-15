<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GoldTrust Finance — Instant Gold Loans</title>
    <meta name="description" content="Get instant gold loans at low interest rates with GoldTrust Finance. High valuation, minimal paperwork, doorstep service.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: -apple-system, "Segoe UI", Roboto, sans-serif; }
        .hero { background: linear-gradient(135deg, #b8860b 0%, #7a5c00 100%); color: #fff; }
        .feature-icon { font-size: 2rem; color: #b8860b; }
        .navbar-brand { font-weight: 700; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="<?php echo base_url(); ?>"><i class="bi bi-gem"></i> GoldTrust Finance</a>
        <div class="ms-auto">
            <a href="#offers" class="btn btn-outline-dark btn-sm me-2">Loan Offers</a>
            <a href="<?php echo base_url('admin/login'); ?>" class="btn btn-dark btn-sm">Staff Login</a>
        </div>
    </div>
</nav>

<header class="hero py-5">
    <div class="container py-5 text-center">
        <h1 class="display-5 fw-bold">Unlock the Value of Your Gold — Instantly</h1>
        <p class="lead mb-4">Trusted by thousands of families for quick, transparent, and secure gold loans across our branch network.</p>
        <a href="#offers" class="btn btn-light btn-lg fw-semibold">Explore Loan Offers</a>
    </div>
</header>

<section class="py-5">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-3">
                <div class="feature-icon mb-2"><i class="bi bi-lightning-charge"></i></div>
                <h5>Instant Disbursal</h5>
                <p class="text-muted small">Cash or bank transfer within minutes of gold evaluation.</p>
            </div>
            <div class="col-md-3">
                <div class="feature-icon mb-2"><i class="bi bi-percent"></i></div>
                <h5>Low Interest Rates</h5>
                <p class="text-muted small">Starting at competitive monthly rates with flexible repayment.</p>
            </div>
            <div class="col-md-3">
                <div class="feature-icon mb-2"><i class="bi bi-shield-check"></i></div>
                <h5>Safe &amp; Insured</h5>
                <p class="text-muted small">Your gold is stored in secure, insured branch vaults.</p>
            </div>
            <div class="col-md-3">
                <div class="feature-icon mb-2"><i class="bi bi-file-earmark-text"></i></div>
                <h5>Minimal Paperwork</h5>
                <p class="text-muted small">Just KYC documents — no income proof or credit checks needed.</p>
            </div>
        </div>
    </div>
</section>

<section id="offers" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Our Loan Offerings</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Regular Gold Loan</h5>
                        <p class="text-muted small">Up to 75% of your gold's market value, tenure up to 12 months.</p>
                        <ul class="list-unstyled small">
                            <li><i class="bi bi-check-circle text-success"></i> Interest from 0.9% per month</li>
                            <li><i class="bi bi-check-circle text-success"></i> Flexible part-payment options</li>
                            <li><i class="bi bi-check-circle text-success"></i> Renewable on maturity</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Express Gold Loan</h5>
                        <p class="text-muted small">Fastest disbursal for urgent cash needs, minimal documentation.</p>
                        <ul class="list-unstyled small">
                            <li><i class="bi bi-check-circle text-success"></i> Disbursal within 30 minutes</li>
                            <li><i class="bi bi-check-circle text-success"></i> Doorstep gold pickup available</li>
                            <li><i class="bi bi-check-circle text-success"></i> Tenure up to 6 months</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Top-Up Gold Loan</h5>
                        <p class="text-muted small">Get additional funds against your existing gold loan as gold rates rise.</p>
                        <ul class="list-unstyled small">
                            <li><i class="bi bi-check-circle text-success"></i> Re-valuation at current gold rate</li>
                            <li><i class="bi bi-check-circle text-success"></i> No need to close existing loan</li>
                            <li><i class="bi bi-check-circle text-success"></i> Quick approval process</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <p class="text-center text-muted small mt-4">*Rates and terms are indicative and subject to branch-level approval and current gold rates.</p>
    </div>
</section>

<footer class="py-4 bg-dark text-light">
    <div class="container text-center small">
        &copy; <?php echo date('Y'); ?> GoldTrust Finance. All rights reserved. &middot;
        <a href="<?php echo base_url('admin/login'); ?>" class="text-light">Staff Login</a>
    </div>
</footer>

</body>
</html>
