<?php
$conn = new mysqli("localhost","root","","tripops_db");
if ($conn->connect_error) die("DB Error");

$slug = $_GET['place'] ?? '';
$stmt = $conn->prepare("SELECT * FROM destination_details WHERE slug=?");
$stmt->bind_param("s",$slug);
$stmt->execute();
$dest = $stmt->get_result()->fetch_assoc();
if(!$dest) die("Destination not found");

$id = $dest['id'];

$gallery = $conn->query("SELECT * FROM destination_gallery WHERE destination_id=$id");
$attractions = $conn->query("SELECT * FROM destination_attractions WHERE destination_id=$id");
$restaurants = $conn->query("SELECT * FROM destination_restaurants WHERE destination_id=$id");
$experiences = $conn->query("SELECT * FROM destination_experiences WHERE destination_id=$id");
$events = $conn->query("SELECT * FROM destination_events WHERE destination_id=$id");

// WEATHER
$apiKey = "b99c39ca3a2621f4a47c6008b4904806";
$city = urlencode($dest['name']);
$weatherData = file_get_contents("https://api.openweathermap.org/data/2.5/weather?q=$city&units=metric&appid=$apiKey");
$weather = json_decode($weatherData, true);

date_default_timezone_set('Asia/Kolkata');
$currentTime = date('D, d M Y H:i A');
?>
<!DOCTYPE html>
<html>
<head>
  <title><?= $dest['name'] ?> | TripOps</title>
  <link rel="stylesheet" href="destination_details.css">
  
</head>
<body>

<!-- HERO -->
<section class="hero">
  <img src="<?= $gallery->fetch_assoc()['image'] ?>">
  <div class="hero-text">
    <h1><?= $dest['name'] ?></h1>
    <p><?= $dest['country'] ?></p>
  </div>
</section>

<!-- TABS -->
<nav class="tabs">
  <button onclick="showTab('overview')" class="active">Overview</button>
  <button onclick="showTab('attractions')">Attractions</button>
  <button onclick="showTab('food')">Food</button>
  <button onclick="showTab('experiences')">Experiences</button>
  <button onclick="showTab('events')">Events</button>
</nav>

<!-- OVERVIEW -->
<section id="overview" class="tab-content active">
  <div class="overview-container">

    <!-- ABOUT -->
    <div class="overview-text-full">
      <span class="badge">About the Destination</span>
      <h2>Welcome to <?= $dest['name'] ?></h2>
      <p class="overview-desc"><?= $dest['description'] ?></p>
    </div>

    <!-- WIDGETS ROW -->
    <div class="overview-widgets">

      <!-- QUICK INFO -->
      <div class="quick-info-widget">
        <h3>Quick Info</h3>
        <div class="info-grid">
          <div class="info-card">
            <strong>Country</strong>
            <span><?= htmlspecialchars($dest['country']) ?></span>
          </div>
          <div class="info-card">
            <strong>Best Time</strong>
            <span><?= htmlspecialchars($dest['best_time']) ?></span>
          </div>
          <div class="info-card">
            <strong>Known For</strong>
            <span><?= htmlspecialchars($dest['highlights']) ?></span>
          </div>
        </div>
      </div>

      <!-- WEATHER -->
      <div class="weather-widget">
        <?php if($weather && isset($weather['main'])): ?>
          <h3>Current Weather</h3>
          <p class="weather-time"><?= $currentTime ?></p><br><br>
          <div class="weather-card">
            <img src="https://openweathermap.org/img/wn/<?= $weather['weather'][0]['icon'] ?>@2x.png">
            <div class="weather-details">
              <span class="temp"><?= round($weather['main']['temp']) ?>°C</span><br>
              <span><?= ucfirst($weather['weather'][0]['description']) ?></span>
              <span>Feels like: <?= round($weather['main']['feels_like']) ?>°C</span>
              <span>Humidity: <?= $weather['main']['humidity'] ?>%</span>
              <span>Wind: <?= $weather['wind']['speed'] ?> m/s</span>
            </div>
          </div>
        <?php else: ?>
          <p>Weather not available</p>
        <?php endif; ?>
      </div>

      <!-- CURRENCY -->
      <div class="quick-info-widget">
        <h3>Currency</h3>
        <div class="info-grid">
          <div class="info-card">
            <strong>Currency</strong>
            <span><?= htmlspecialchars($dest['currency_name']) ?></span>
          </div>
          <div class="info-card">
            <strong>Code</strong>
            <span><?= htmlspecialchars($dest['currency_code']) ?></span>
          </div>
          <div class="info-card">
            <strong>Symbol</strong>
            <span><?= htmlspecialchars($dest['currency_symbol']) ?></span>
          </div>
        </div>
      </div>

    </div> <!-- overview-widgets -->
  </div> <!-- overview-container -->
  <!-- commongestures -->
</section>

<!-- ATTRACTIONS -->
<section id="attractions" class="tab-content">
  <div class="grid">
    <?php while($a=$attractions->fetch_assoc()): ?>
      <div class="card">
        <img src="attractions/<?= $a['image'] ?>" alt="<?= htmlspecialchars($a['name']) ?>">
        <h3><?= $a['name'] ?></h3>
        <p><?= $a['description'] ?></p>
      </div>
    <?php endwhile; ?>
  </div>
</section>


<!-- RESTAURANTS -->
<section id="food" class="tab-content">
  <div class="grid">
    <?php while($r = $restaurants->fetch_assoc()): ?>
      <div class="card">
        <img src="food/<?= htmlspecialchars($r['image']) ?>" alt="<?= htmlspecialchars($r['name']) ?>">
        <h3>
          <?= htmlspecialchars($r['name']) ?>
          <!-- Star Ratings -->
          <span class="stars" style="color: gold; font-size: 16px; margin-left: 5px;">
            <?php
            $rating = $r['rating'] ?? 5; // default 5 stars
            for ($i = 1; $i <= 5; $i++) {
                echo ($i <= $rating) ? '★' : '☆';
            }
            ?>
          </span>
        </h3>
        <p><strong>Cuisine:</strong> <?= htmlspecialchars($r['cuisine']) ?></p>
        <p><?= htmlspecialchars($r['description']) ?></p>
      </div>
    <?php endwhile; ?>
  </div>
</section>


<!-- EXPERIENCES -->
<section id="experiences" class="tab-content">
  <div class="grid">
    <?php while($e = $experiences->fetch_assoc()): ?>
      <div class="card">
        <img src="experiences/<?= htmlspecialchars($e['image']) ?>" alt="<?= htmlspecialchars($e['name']) ?>">
        <h3><?= htmlspecialchars($e['name']) ?></h3>
        <p><?= htmlspecialchars($e['description']) ?></p>
      </div>
    <?php endwhile; ?>
  </div>
</section>


<!-- EVENTS -->
<section id="events" class="tab-content">
  <div class="grid">
    <?php while($ev = $events->fetch_assoc()): ?>
      <div class="card">
        <img src="events/<?= htmlspecialchars($ev['image']) ?>" alt="<?= htmlspecialchars($ev['name']) ?>">
        <h3><?= htmlspecialchars($ev['name']) ?></h3>
        <p><?= htmlspecialchars($ev['description']) ?></p>
        <strong><?= date('d M Y', strtotime($ev['start_date'])) ?> → <?= date('d M Y', strtotime($ev['end_date'])) ?></strong>
      </div>
    <?php endwhile; ?>
  </div>
</section>
<!-- Hero or wherever you want the button -->
<section class="get-started-section">
<button class="get-started" 
onclick="window.location.href='trip_dashboard.php?place=<?= urlencode($slug) ?>'">Start Booking</button>

</section>




<script>
function showTab(id){
  document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.tabs button').forEach(b => b.classList.remove('active'));
  document.getElementById(id).classList.add('active');
  event.target.classList.add('active');
}
</script>

</body>
</html>
<?php
// Get the current filename
$current_file = basename($_SERVER['PHP_SELF']);

// Only show if the current page is NOT landing.php
if ($current_file !== 'landing.php') {
    include 'popup.php';
}
?>