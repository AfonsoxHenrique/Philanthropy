<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Phill Antrophy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="style/master.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <?php include 'include/navbar.php' ?>

    <!-- Hero Section -->
    <section id="hero" class="d-flex align-items-center justify-content-center homeSec">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h1 class="mb-4">Empowering Our Community, One Act of Giving at a Time</h1>
                    <p>Phill Antrophy helps close the resource gap for neighborhood schools. We fund extra-curricular activities, provide baseline school supplies
                        and support community wellness initiatives.
                    </p>
                </div>
                <div class="col-md-8 text-end">
                    <img src="assets/images/hero_image.jpg" class="img-fluid">
                </div>
            </div>
            <div class="card fundraising-card">
                <div class="card-body p-4">

                    <!-- Top row: label + amount on the left, badge on the right -->
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="fund-label text-uppercase">Current Fundraising Status</small>
                            <div>
                                <span class="fund-raised">12,450</span>
                                <span class="fund-goal">raised of 20,000 goal</span>
                            </div>
                        </div>
                        <span class="badge rounded-pill fund-badge">62% Funded</span>
                    </div>

                    <!-- Progress bar -->
                    <div class="progress fund-progress my-3" role="progressbar"
                        aria-valuenow="62" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar fund-bar" style="width: 62%"></div>
                    </div>

                    <!-- Bottom row -->
                    <div class="d-flex justify-content-between">
                        <small class="fund-note">Supported by over 340 active families &amp; local businesses</small>
                        <small class="fund-deadline">Goal Ends: April 15, 2026</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Commitments Section -->
    <section id="commitment" class="d-flex align-items-center justify-content-center homeSec">
        <div class="container-fluid">
            <h2 class="text-center mb-5">Our Core Commitment</h2>
            <p>We believe that every child deserves a level playing field, health nutrition, and safe outlets for after-school
                development. Through the collaborative funding of local parents and regional patrons, we build direct, transparent
                relief structures for our school cluster.</p>
        </div>
    </section>

    <!-- Projects Section -->
    <section id="projects" class="d-flex align-items-center justify-content-center homeSec">
        <div class="container-fluid">
            <h4 class="text-center mb-1">STORIES FROM THE GROUND</h4>
            <h2 class="text-center mb-5">What We Are Funding This Season</h2>

            <div class="row g-4">
                <div class="row g-4">
                    <!-- Potential project -->
                    <div class="col-md-4">
                        <div class="card">
                            <img src="img/hp400.jpg" class="samsungimg">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Essential School Supplies</h5>

                                <p class="card-text">
                                    backpacks, stationery, and tech toolkits for lower-income students
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Potential project -->
                    <div class="col-md-4">
                        <div class="card">
                            <img src="img/hp400.jpg" class="samsungimg">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Health After-School Fuel</h5>

                                <p class="card-text">
                                    Daily hot meals and nutritious snacks for students staying late for athletic or academic coaching
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Potential project -->
                    <div class="col-md-4">
                        <div class="card">
                            <img src="img/hp400.jpg" class="samsungimg">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Creative Arts Program</h5>

                                <p class="card-text">
                                    Funding instruments, canvases, and community art galleries to unlock self-expression and joy
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Events Section -->
    <section id="events" class="d-flex align-items-center justify-content-center homeSec">
        <div class="container-fluid">
            <h4>MARK YOUR CALENDARS</h4>
            <h2 class="mb-5">Upcoming Neighborhood Events</h2>
            <div class="row g-4">
                <div class="row g-4">
                    <!-- Potential project -->
                    <div class="col-md-4">
                        <div class="card">
                            <img src="img/hp400.jpg" class="samsungimg">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Essential School Supplies</h5>

                                <p class="card-text">
                                    backpacks, stationery, and tech toolkits for lower-income students
                                </p>
                                <div class="mt-auto">
                                    <a href="#" class="btn btn-primary btn-sm me-2">Learn More</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Potential project -->
                    <div class="col-md-4">
                        <div class="card">
                            <img src="img/hp400.jpg" class="samsungimg">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Health After-School Fuel</h5>

                                <p class="card-text">
                                    Daily hot meals and nutritious snacks for students staying late for athletic or academic coaching
                                </p>
                                <div class="mt-auto">
                                    <a href="#" class="btn btn-primary btn-sm me-2">Learn More</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Potential project -->
                    <div class="col-md-4">
                        <div class="card">
                            <img src="img/hp400.jpg" class="samsungimg">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">Creative Arts Program</h5>

                                <p class="card-text">
                                    Funding instruments, canvases, and community art galleries to unlock self-expression and joy
                                </p>
                                <div class="mt-auto">
                                    <a href="#" class="btn btn-primary btn-sm me-2">Learn More</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to action Section -->
    <section id="callToAction" class="d-flex align-items-center justify-content-center homeSec">
        <div class="container-fluid">
            <h2 class="text-center mb-5">Join Us - Every Contribution Makes a Difference</h2>
            <p>Whether you can give 5 dollars, 2 hours of your time, or share our links, you are helping construct a robust foundation of securit for local school children</p>
            <button>Complete Donation</button>
            <button>Volunteer With Us</button>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Scroll Effect -->
    <script>
        window.addEventListener("scroll", function() {
            const navbar = document.querySelector(".custom-navbar");
            navbar.classList.toggle("scrolled", window.scrollY > 50);
        });
    </script>

</body>

</html>