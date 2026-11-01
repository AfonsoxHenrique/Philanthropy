<?php
// Set $activePage on each admin page before including this file
$activePage = $activePage ?? "";

$menuItems = [
    "dashboard"  => ["label" => "Dashboard Overview",  "icon" => "bi-grid",              "link" => "dashboard.php"],
    "homepage"   => ["label" => "Homepage Content",    "icon" => "bi-file-earmark-text", "link" => "homepage-content.php"],
    "events"     => ["label" => "District Events",     "icon" => "bi-calendar-event",    "link" => "events.php"],
    "faqs"       => ["label" => "FAQs Management",     "icon" => "bi-question-circle",   "link" => "faqs.php"],
    "donations"  => ["label" => "Donations Ledger",    "icon" => "bi-currency-dollar",   "link" => "donations.php"],
    "volunteers" => ["label" => "Volunteer Stewards",  "icon" => "bi-people",            "link" => "volunteers.php"],
    "settings"   => ["label" => "Settings",            "icon" => "bi-gear",              "link" => "settings.php"]
];
?>
<!-- Admin Sidebar (static on desktop, slide-out menu on mobile) -->
<div class="admin-sidebar-wrap">
    <aside class="offcanvas-lg offcanvas-start admin-sidebar" tabindex="-1" id="adminSidebar" aria-label="Admin menu">
        <div class="offcanvas-header d-lg-none">
            <span class="fw-bold">Menu</span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                data-bs-target="#adminSidebar" aria-label="Close menu"></button>
        </div>
        <div class="offcanvas-body">
            <nav class="w-100">
                <ul class="nav flex-column gap-1">
                    <?php foreach ($menuItems as $key => $item): ?>
                        <li class="nav-item">
                            <a href="<?php echo $item["link"]; ?>"
                                class="nav-link sidebar-link <?php echo $activePage === $key ? "active" : ""; ?>"
                                <?php echo $activePage === $key ? 'aria-current="page"' : ""; ?>>
                                <i class="bi <?php echo $item["icon"]; ?>"></i>
                                <?php echo $item["label"]; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </aside>
</div>