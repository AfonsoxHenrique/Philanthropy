<head>
    <style>
        .site-footer {
            background-color: #1f3d2b;
            color: #fff;
            padding: 30px 120px 20px;
        }

        /* Logo + name */
        .footer-logo {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #e07b00;
            color: #1f3d2b;
            border-radius: 8px;
            font-size: 1.1rem;
        }

        .footer-brand {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .footer-text {
            max-width: 340px;
            font-size: 0.9rem;
            line-height: 1.7;
            margin-bottom: 1.25rem;
        }

        /* Social circles */
        .social-icon {
            width: 33px;
            height: 33px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #2d5a43;
            color: #fff;
            border-radius: 50%;
            font-size: 0.85rem;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .social-icon:hover {
            background-color: #e07b00;
            color: #fff;
            transform: translateY(-2px);
        }

        /* Column headings */
        .footer-heading {
            color: #e07b00;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        /* Contact list */
        .footer-contact li {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            margin-bottom: 0.6rem;
        }

        .footer-contact i {
            color: #e07b00;
        }

        /* Quick links */
        .footer-links li {
            margin-bottom: 0.85rem;
        }

        .footer-links a,
        .footer-bottom a {
            color: #fff;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .footer-links a:hover,
        .footer-bottom a:hover {
            color: #e07b00;
        }

        /* Divider + bottom row */
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            margin-top: 2rem;
            padding-top: 2rem;
        }

        .footer-bottom small {
            font-size: 0.8rem;
        }

        .footerLogo {
            width: 100px;
            height: auto;
        }
    </style>
</head>
<footer class="site-footer">
    <div class="container-fluid px-4 px-lg-5">

        <div class="row gy-5">

            <!-- Brand -->
            <div class="col-lg-6">
                <img src="assets/images/footerLogo2.png" class="footerLogo">
                <p class="footer-text">
                    Empowering students, supporting local families, and building a
                    stronger school community through strategic, transparent fundraising.
                </p>
                <div class="d-flex gap-3">
                    <a href="#" class="social-icon" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-icon" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>

            <!-- Contact -->
            <div class="col-sm-6 col-lg-4">
                <h6 class="footer-heading">Contact Us</h6>
                <ul class="list-unstyled footer-contact">
                    <li><i class="bi bi-envelope"></i> phillanthropy.au@gmail.com</li>
                    <li><i class="bi bi-telephone"></i> +61 459 694 894</li>
                    <li><i class="bi bi-geo-alt"></i> 44-46 Canterbury Road, Blackburn South, VIC</li>
                </ul>
            </div>

            <!-- Quick links -->
            <div class="col-sm-6 col-lg-2">
                <h6 class="footer-heading">Quick Links</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="./privacypolicy.php">Privacy Policy</a></li>
                    <li><a href="./terms.php">Terms of Use</a></li>
                    <li><a href="./faq.php">FAQ</a></li> 
                    <li><a href="./contacts.php">Contacts</a></li>
                </ul>
            </div>

        </div>

        <!-- Bottom bar -->
        <div class="footer-bottom d-flex justify-content-center">
            <small>&copy; 2026 Phill Anthropy Ltd. ABN: 85675428896</small>
        </div>

    </div>
</footer>