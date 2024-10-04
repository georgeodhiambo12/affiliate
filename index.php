<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TenderSoko Affiliate Program</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0-beta3/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }

        header {
            background-color: #004c66;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        header img {
            height: 50px;
        }

        nav {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            background-color: #004c66;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin: 0 20px;
            font-weight: bold;
        }

        nav a:hover {
            text-decoration: underline;
        }

        /* Mobile View Toggle Button */
        .menu-toggle {
            display: none;
            font-size: 24px;
            cursor: pointer;
        }

        /* Responsive Styling */
        @media (max-width: 768px) {
            nav {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 70px;
                right: 0;
                background-color: #004c66;
                width: 100%;
                padding: 0;
            }

            nav a {
                padding: 15px;
                border-bottom: 1px solid #fff;
                text-align: center;
            }

            nav a:last-child {
                border-bottom: none;
            }

            .menu-toggle {
                display: block;
            }

            nav.active {
                display: flex;
            }
        }

        /* Hero Section */
        .hero {
            background-color: #005b80;
            color: white;
            text-align: center;
            padding: 100px 20px;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 1.2rem;
        }

        .hero button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px;
        }

        /* About Affiliate Program Section */
        .section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
            background-color: #fff;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 2rem;
            color: #004c66;
            text-align: center;
            margin-bottom: 40px;
        }

        /* Flexbox for image and text side by side */
        .about-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 0 auto;
            max-width: 1000px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 1s ease-in-out forwards;
        }

        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(50px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .about-content img {
            width: 45%; 
            border-radius: 10px;
            margin-right: 20px;
            opacity: 0;
            transform: translateX(-100%);
            animation: slideInLeft 1s ease-in-out forwards;
        }

        .about-content .about-text {
            width: 50%;
            text-align: left;
            opacity: 0;
            transform: translateX(100%);
            animation: slideInRight 1s ease-in-out forwards;
            animation-delay: 0.3s; /* Delay text slightly to come after image */
        }

        @keyframes slideInLeft {
            0% {
                opacity: 0;
                transform: translateX(-100%);
            }
            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            0% {
                opacity: 0;
                transform: translateX(100%);
            }
            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Features Section */
        .features {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .feature {
            width: 30%;
            padding: 20px;
            background-color: #f9f9f9;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .feature img {
            width: 70px;
            height: 70px;
            margin-bottom: 20px;
        }

        /* Commissions Section */
        .commissions-section {
            padding: 60px 20px;
            background-color: #28a745;
            color: white;
            text-align: center;
        }

        .commissions-section h2 {
            font-size: 2.5rem;
            margin-bottom: 30px;
        }

        .commission-card {
            width: 30%;
            background-color: white;
            color: #004c66;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .commission-card h3 {
            font-size: 1.8rem;
        }

        .commission-card p {
            font-size: 1.1rem;
        }

        /* FAQ Section */
        .accordion {
            width: 100%;
            margin-top: 40px;
            text-align: left;
        }

        .accordion-toggle {
            background-color: #004c66;
            color: white;
            padding: 15px;
            width: 100%;
            text-align: left;
            border: none;
            cursor: pointer;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .accordion-content {
            background-color: #f9f9f9;
            padding: 15px;
            display: none;
            border-radius: 5px;
        }

        /* Footer */
        footer {
            background-color: #004c66;
            color: white;
            padding: 20px;
            text-align: center;
        }

        footer ul {
            list-style: none;
            padding: 0;
        }

        footer ul li {
            display: inline-block;
            margin-right: 20px;
        }

        footer a {
            color: white;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        .social-icons {
            margin-top: 20px;
        }

        .social-icons a {
            color: white;
            margin: 0 10px;
            font-size: 20px;
        }

        .contact-info {
            margin-top: 30px;
            padding: 20px;
            background-color: #fff;
            color: #004c66;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .contact-info p {
            margin: 10px 0;
            font-size: 1.1rem;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header>
        <a href="#"><img src="images/tendersoko.jpg" alt="TenderSoko Logo"></a>
        <span class="menu-toggle" onclick="toggleMenu()">&#9776;</span>
        <nav id="navbar">
            <a href="#about">About Affiliate</a>
            <a href="#features">Key Features</a>
            <a href="#commissions">Commissions</a>
            <a href="#faq">FAQs</a>
            <a href="login.php">Login</a>
            <a href="registration.html">Join Us</a>
        </nav>
    </header>

    <!-- Hero Section -->
    <div class="hero">
        <h1>Join the TenderSoko Affiliate Program</h1>
        <p>Promote our services and earn lucrative commissions</p>
        <button onclick="window.location.href='registration.html'">Join Now</button>
    </div>

    <!-- About Affiliate Program Section -->
    <div id="about" class="section">
        <h2 class="section-title">What is TenderSoko Affiliate Program?</h2>

        <!-- Image on the left, text on the right -->
        <div class="about-content">
            <img src="images/affiliate.png" alt="Affiliate Benefits">
            <div class="about-text">
                <p>The TenderSoko Affiliate Program is a rewarding way to earn income by promoting our platform.</p>
                <p>We provide all the tools, tracking, and support you need to succeed. Sign up, share your unique affiliate link, and start earning commissions for each customer you refer!</p>
                <p>By joining our program, you’ll not only earn generous commissions for successful referrals but also have access to our extensive resources that simplify promoting our services. Whether you're new to affiliate marketing or an experienced marketer, our easy-to-use platform empowers you to grow your network, track your performance, and receive timely payouts.</p>
                <p>As a TenderSoko affiliate, you can tap into a network of opportunities, attract businesses and individuals looking for tenders, and benefit from a well-established, trusted platform. You’ll have real-time access to your referral statistics and can leverage marketing materials that maximize your outreach and earnings potential.</p>
            </div>
        </div>
    </div>

    <!-- Key Features Section -->
    <div id="features" class="section">
        <h2 class="section-title">Key Features</h2>
        <div class="features">
            <div class="feature">
                <img src="images/dollar.png" alt="High Commissions">
                <h3>High Commissions</h3>
                <p>Earn up to 10% commission for every successful referral.</p>
            </div>
            <div class="feature">
                <img src="images/time.png" alt="Real-Time Tracking">
                <h3>Real-Time Tracking</h3>
                <p>Get instant updates on your earnings and performance.</p>
            </div>
            <div class="feature">
                <img src="images/easy.png" alt="User-Friendly">
                <h3>User-Friendly</h3>
                <p>Our platform is easy to use, no matter your experience level.</p>
            </div>
        </div>
    </div>

    <!-- Commissions Section -->
    <div id="commissions" class="commissions-section">
        <h2>Earn Commissions with Every Referral</h2>
        <div class="features">
            <div class="commission-card">
                <h3>Up to 10% Commission</h3>
                <p>Enjoy high commission rates on all qualifying referrals.</p>
            </div>
            <div class="commission-card">
                <h3>Real-Time Payouts</h3>
                <p>Get paid quickly and easily with our real-time payout system.</p>
            </div>
            <div class="commission-card">
                <h3>Track Your Earnings</h3>
                <p>Monitor your progress and earnings with our affiliate dashboard.</p>
            </div>
        </div>
    </div>

    <!-- FAQs Section -->
    <div id="faq" class="section">
        <h2 class="section-title">Frequently Asked Questions</h2>
        <div class="accordion">
            <button class="accordion-toggle">What is the TenderSoko Affiliate Program?</button>
            <div class="accordion-content">
                <p>The TenderSoko Affiliate Program allows you to earn commissions by promoting our services. Sign up, share your affiliate link/code, and earn rewards for every successful referral!</p>
            </div>

            <button class="accordion-toggle">How do I join the program?</button>
            <div class="accordion-content">
                <p>Simply click the "Join Now" button and complete the registration form. Once approved, you'll get access to your unique affiliate link and dashboard.</p>
            </div>

            <button class="accordion-toggle">How much can I earn?</button>
            <div class="accordion-content">
                <p>We offer competitive commission rates of up to 10% on qualifying referrals. Your earning potential is limitless based on how many customers you refer.</p>
            </div>

            <button class="accordion-toggle">How do I track my referrals?</button>
            <div class="accordion-content">
                <p>We provide a real-time tracking dashboard where you can monitor your referrals, earnings, and overall performance.</p>
            </div>

            <button class="accordion-toggle">When do I get paid?</button>
            <div class="accordion-content">
                <p>Payouts are processed every two weeks and end of the month once your referral meets the qualifying criteria. You can update your preferred payment method on your profile once you log in.</p>
            </div>
        </div>
    </div>

    <!-- Contact Information Section -->
    <div class="contact-info">
        <h2>Contact Information</h2>
        <p>KE: +254 20 2006063</p>
        <p>+254 720 375992, +254783600600</p>
        <p>UG: +256 777 931738</p>
        <p>P.O. BOX 1233-00618 NAIROBI, KENYA</p>
        <p>Garden Estate road, Off Thika road</p>
    </div>

    <!-- Footer Section -->
    <footer>
        <ul>
            <li><a href="privacy.html">Privacy Policy</a></li>
            <li><a href="tc.html">Terms of Service</a></li>
        </ul>
        <div class="social-icons">
            <a href="https://www.instagram.com/tendersoko/"><i class="fab fa-instagram"></i></a>
            <a href="mailto:info@tendersoko.com"><i class="fas fa-envelope"></i></a>
            <a href="https://www.facebook.com/officialTenderSoko"><i class="fab fa-facebook"></i></a>
            <a href="https://x.com/TenderSoko"><i class="fab fa-x-twitter"></i></a>
            <a href="https://www.tiktok.com/@tendersoko"><i class="fab fa-tiktok"></i></a>
        </div>
        <p>&copy; 2024 TenderSoko Limited. All rights reserved.</p>
    </footer>

    <script>
        // Accordion functionality
        const toggles = document.querySelectorAll('.accordion-toggle');
        toggles.forEach(toggle => {
            toggle.addEventListener('click', function () {
                const content = this.nextElementSibling;
                content.style.display = content.style.display === 'block' ? 'none' : 'block';
            });
        });

        // Toggle menu
        function toggleMenu() {
            const nav = document.getElementById("navbar");
            nav.classList.toggle("active");
        }
    </script>
</body>

</html>
