<head>
    <style>
        /*Navigation Bar Styling*/
        .custom-navbar {
            padding: 15px 120px;
            transition: background 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .custom-navbar.scrolled {
            background: #faf9f9;
        }

        .navBarLogo {
            width: auto;
            height: 70px;
        }

        .nav-link {
            position: relative;
            color: #2D5A43 !important;
            font-size: 1.1rem;
            font-weight: 500;
            margin: 0 10px;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #d97706 !important;
        }

        .nav-link::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -4px;
            width: 0%;
            height: 2px;
            background-color: #d97706;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /*Donate Button Styling*/
        .donate-btn {
            background-color: #2d5a43;
            color: #fff;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 14px 32px;
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .donate-btn:hover {
            background-color: #234736;
            color: #fff;
            transform: translateY(-2px);
        }

        .donate-btn:active {
            background-color: #1c3a2c;
            color: #fff;
        }

        .navbar .donate-btn {
            padding: 8px 20px;
            font-size: 1rem;
        }
    </style>
</head>
<nav class="navbar navbar-expand-lg custom-navbar">
    <div class="container-fluid">

        <a class="navbar-brand" href="#"><img src="./assets/images/navBarLogo2.png" class="navBarLogo"></a>

        <!-- Mobile Viewing Menu Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu HyperLinks -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav align-items-center mx-lg-auto gap-3">
                <a class="nav-link" href="./index.php">Home</a>
                <a class="nav-link" href="./about.php">About Us</a>
                <a class="nav-link" href="./donationPage.php">Donate</a>
                <a class="nav-link" href="./events.php">Events</a>
                <a class="nav-link" href="./volunteer.php">Volunteer</a>
                <a class="nav-link" href="./gallery.php">Gallery</a>
            </div>
            <button class="btn donate-btn d-flex d-lg-inline-flex align-items-center gap-2 mx-auto mx-lg-0 mt-3 mt-lg-0">
                Donate Now
                <i class="bi bi-heart"></i>
            </button>
        </div>


    </div>
</nav>