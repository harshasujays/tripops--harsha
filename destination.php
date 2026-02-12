<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: landing.php");
    exit;
}
$current_page = 'destination';

// Database connection
$conn = new mysqli("localhost","root","","tripops_db");
if($conn->connect_error){
    die("Connection failed: ".$conn->connect_error);
}

// Fetch destinations
$destinations = [];
// Fetch 3 random trending destinations
$trending = [];
$trendSql = "SELECT * FROM destinations ORDER BY RAND() LIMIT 3";
$trendResult = $conn->query($trendSql);

if ($trendResult->num_rows > 0) {
    while ($row = $trendResult->fetch_assoc()) {
        $trending[] = $row;
    }
}

$sql = "SELECT * FROM destinations";
$result = $conn->query($sql);
if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $destinations[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>TripOps – Destination</title>
    <link rel="stylesheet" href="destination.css">
    <link rel="stylesheet"href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

</head>
<body>

<section class="hero destination-hero">
    <div class="bg bg1"></div>
    <div class="bg bg2"></div>
    <div class="hero-overlay"></div>

    <div class="hero-top">
        <div class="logo">TripOps</div>
        <div class="nav-menu">
            <a href="landing.php" class="<?= $current_page=='landing'?'active':'' ?>">HOME</a>
            <a href="destination.php" class="<?= $current_page=='destination'?'active':'' ?>">DESTINATION</a>
            <a href="#">ABOUT US</a>
        </div>
        <div class="user-profile">
            <div class="profile-circle">
                <?php
                if (!empty($_SESSION["profile_pic"]) && file_exists($_SESSION["profile_pic"])) {
                    echo '<img src="'.$_SESSION["profile_pic"].'?t='.time().'">';
                } else {
                    echo strtoupper(substr($_SESSION["user"], 0, 1));
                }
                ?>
            </div>
        </div>
    </div>

    <div class="hero-content">
        <h1>Find Your Perfect Destination</h1>
        <p>Search for attractions, and experiences</p>

        <!-- SEARCH -->
        <div class="search-bar" style="position:relative; max-width:420px;">
            <input
                type="text"
                id="destinationInput"
                placeholder="Where to?"
                autocomplete="off"
            >
            <button id="searchBtn" type="button">Search</button>
            <div id="suggestionsBox" class="suggestions-box"></div>
        </div>
    </div>
</section>

<!-- ================= EXPLORE BY DESTINATION ================= -->
<section class="explore-destinations">
    <h2>Explore by Destination</h2>
    <p class="section-subtitle">
        Swipe or scroll to explore popular destinations
    </p>


    <div class="scroll-wrapper">
        <button class="scroll-btn left" onclick="scrollDestinations(-1)">&#10094;</button>
        <div class="destination-scroll" id="destinationScroll">
            <?php foreach($destinations as $dest): ?>
                <a href="destination_details.php?place=<?= urlencode($dest['slug']) ?>"
                   class="destination-card"
                   data-category="<?= $dest['category'] ?>">
                    <img src="<?= $dest['image'] ?>">
                    <div class="card-overlay">
                        <h3><?= $dest['name'] ?></h3>
                        <span><?= $dest['country'] ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <button class="scroll-btn right" onclick="scrollDestinations(1)">&#10095;</button>
    </div>
</section>
<!-- ================= TRENDING NOW ================= -->
<section class="trending-section">
    <div class="trending-header">
        <h2>Trending Now 🔥</h2>
        <p>Destinations travellers are loving right now</p>
    </div>

    <div class="trending-grid">
        <?php foreach ($trending as $place): ?>
            <a href="destination_details.php?place=<?= urlencode($place['slug']) ?>"
               class="trending-card">

                <img src="<?= $place['image'] ?>" alt="<?= $place['name'] ?>">

                <div class="trending-overlay">
                    <span class="trending-badge">Trending</span>
                    <h3><?= $place['name'] ?></h3>
                    <p><?= $place['country'] ?> • <?= ucfirst($place['category']) ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="map-section">
    <div class="map-header">
        <h2>Explore the World</h2>
        <p>Every destination you love, all in one place</p>
    </div>

    <div class="map-wrapper">
        <div id="map"></div>
    </div>
</section>


<script>
// ================= CLIENT-SIDE SEARCH =================
const input = document.getElementById("destinationInput");
const box = document.getElementById("suggestionsBox");
const searchBtn = document.getElementById("searchBtn");

// Dynamic destinations from PHP
const localDestinations = [
<?php foreach($destinations as $dest){
    echo '"'.$dest['name'].', '.$dest['country'].'",';
} ?>
];

function showSuggestions(arr) {
    box.innerHTML = "";
    if (!arr.length) { box.style.display = "none"; return; }
    arr.forEach(place => {
        const div = document.createElement("div");
        div.className = "suggestion-item";
        div.textContent = place;
        div.addEventListener("mousedown", () => {
            input.value = place;
            box.style.display = "none";
        });
        box.appendChild(div);
    });
    box.style.display = "block";
}

let debounceTimer;
input.addEventListener("input", () => {
    clearTimeout(debounceTimer);
    const q = input.value.trim();
    if (q.length < 2) { box.style.display = "none"; return; }
    debounceTimer = setTimeout(() => {
        const matches = localDestinations.filter(d => d.toLowerCase().startsWith(q.toLowerCase())).slice(0, 7);
        showSuggestions(matches);
    }, 100);
});

searchBtn.addEventListener("click", () => {
    const place = input.value.trim();
    if (!place) { alert("Please enter a destination."); return; }
    const slug = place.split(',')[0].toLowerCase();
    window.location.href = `destination_details.php?place=${encodeURIComponent(slug)}`;
});

document.addEventListener("click", e => {
    if (!e.target.closest(".search-bar")) box.style.display = "none";
});

// ================= HERO SLIDESHOW =================
const images = ['hallstatt.jpg','sydney.jpg','zurich.jpg','dest4.jpg','queen.jpg','dest5.jpg','sydney.jpg','dest1.jpg'];
let index = 0, showFirst = true;
const bg1 = document.querySelector('.bg1');
const bg2 = document.querySelector('.bg2');
bg1.style.backgroundImage = `url(${images[index]})`; index++;
setInterval(() => {
    const current = showFirst ? bg2 : bg1;
    const previous = showFirst ? bg1 : bg2;
    current.style.backgroundImage = `url(${images[index]})`;
    current.style.opacity = '1';
    previous.style.opacity = '0';
    showFirst = !showFirst;
    index = (index + 1) % images.length;
}, 4000);

function scrollDestinations(direction) {
    const container = document.getElementById("destinationScroll");
    const scrollAmount = 280;
    container.scrollLeft += direction * scrollAmount;
}
// ================= MAP DATA =================
const mapDestinations = [
<?php foreach ($destinations as $d): ?>
    {
        name: "<?= $d['name'] ?>",
        country: "<?= $d['country'] ?>",
        slug: "<?= $d['slug'] ?>"
    },
<?php endforeach; ?>
];
// ================= LEAFLET MAP =================
const map = L.map('map').setView([20, 0], 2); // world view

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

mapDestinations.forEach(place => {
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(place.name + ', ' + place.country)}`)
        .then(res => res.json())
        .then(data => {
            if (data.length > 0) {
                const lat = data[0].lat;
                const lon = data[0].lon;

                const customIcon = L.divIcon({
    className: 'custom-marker',
    html: `<div class="marker-dot"></div>`,
    iconSize: [14, 14],
    iconAnchor: [7, 7]
});

L.marker([lat, lon], { icon: customIcon })
    .addTo(map)
    .bindPopup(`
        <strong>${place.name}</strong><br>
        ${place.country}<br>
        <a href="destination_details.php?place=${place.slug}">
            View details →
        </a>
    `);

            }
        });
});

</script>

</body>
</html>
