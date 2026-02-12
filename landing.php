<?php
session_start();

/* ================= DATABASE SETUP ================= */
$host = "localhost";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) die("DB Error");

$conn->query("CREATE DATABASE IF NOT EXISTS tripops_db");
$conn->select_db("tripops_db");

$conn->query("
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    profile_pic VARCHAR(255) DEFAULT ''
)
");

$conn->query("
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    rating INT NOT NULL,
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

$message = "";

/* ================= AUTH HANDLERS ================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["signup"])) {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $rawPassword = $_POST["password"];

    // ---------------- PASSWORD VALIDATION ----------------
    if(strlen($rawPassword) < 8) {
        $message = "Password must be at least 8 characters long.";
        $openSignupModal = true;
    } elseif(!preg_match("/[A-Z]/", $rawPassword)) {
        $message = "Password must contain at least one uppercase letter.";
        $openSignupModal = true;
    } elseif(!preg_match("/[\W_]/", $rawPassword)) {
        $message = "Password must contain at least one special character.";
        $openSignupModal = true;
    } else {
        // Hash the password only if it passes validation
        $password = password_hash($rawPassword, PASSWORD_DEFAULT);

        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $message = "You already have an account. Please login instead.";
            $openSignupModal = true;
        } else {
            $stmt = $conn->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
            $stmt->bind_param("sss", $name, $email, $password);
            if ($stmt->execute()) {
                $_SESSION["user"] = $name;
                $_SESSION["user_id"] = $conn->insert_id;
                $_SESSION["profile_pic"] = null;

                header("Location: landing.php");
                exit;
            } else {
                $message = "Signup failed. Try again.";
                $openSignupModal = true;
            }
        }
    }
}

    // ---------------- LOGIN ----------------
    if (isset($_POST["login"])) {
        $email = $_POST["email"];
        $password = $_POST["password"];

        $stmt = $conn->prepare("SELECT id,name,password,profile_pic FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $row = $res->fetch_assoc();
            if (password_verify($password, $row["password"])) {
                $_SESSION["user"] = $row["name"];
                $_SESSION["user_id"] = $row["id"];
                $_SESSION["profile_pic"] = $row["profile_pic"];
            } else {
                $message = "Invalid password.";
                $openLoginModal = true;
            }
        } else {
            $message = "User not found.";
            $openLoginModal = true;
        }
    }

    // ---------------- LOGOUT ----------------
    if (isset($_POST["logout"])) {
        session_unset();
        session_destroy();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    // ---------------- UPLOAD PROFILE PIC ----------------
    if (isset($_FILES["profile_image"]) && isset($_SESSION["user_id"])) {
        $file = $_FILES["profile_image"];
        $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
        $targetDir = "uploads/";
        if(!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $targetFile = $targetDir . "profile_" . $_SESSION["user_id"] . "." . $ext;

        // Remove old profile pics
        $oldFiles = glob($targetDir . "profile_" . $_SESSION["user_id"] . ".*");
        foreach($oldFiles as $old){
            if($old !== $targetFile) unlink($old);
        }

        move_uploaded_file($file["tmp_name"], $targetFile);
        $stmt = $conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
        $stmt->bind_param("si", $targetFile, $_SESSION["user_id"]);
        $stmt->execute();
        $_SESSION["profile_pic"] = $targetFile;
    }

    // ---------------- CHANGE PASSWORD ----------------
    if (isset($_POST["change_password"]) && isset($_SESSION["user_id"])) {
        $current = $_POST["current_password"];
        $new = $_POST["new_password"];

        $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
        $stmt->bind_param("i", $_SESSION["user_id"]);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            if (password_verify($current, $row["password"])) {
                $newHash = password_hash($new, PASSWORD_DEFAULT);
                $stmt2 = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                $stmt2->bind_param("si", $newHash, $_SESSION["user_id"]);
                $stmt2->execute();
                $message = "Password changed successfully!";
                $passwordSuccess = true;
            } else {
                $message = "Current password is incorrect.";
                $passwordError = true;
            }
        } else {
            $message = "Something went wrong. Please try again.";
            $passwordError = true;
        }
    }

    // ---------------- POST REVIEW ----------------
    if(isset($_POST['submit_review']) && isset($_SESSION['user_id'])) {
        $rating = intval($_POST['rating']);
        $reviewText = $_POST['review'] ?? '';

        $stmt = $conn->prepare("INSERT INTO reviews (user_id, rating, review) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $_SESSION['user_id'], $rating, $reviewText);
        $stmt->execute();
        header("Location: ".$_SERVER['PHP_SELF']."#reviews"); // refresh to show review
        exit;
    }

    // ---------------- DELETE REVIEW ----------------
    if(isset($_POST['delete_review']) && isset($_SESSION['user_id'])) {
        $review_id = intval($_POST['review_id']);
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $review_id, $_SESSION['user_id']);
        $stmt->execute();
        header("Location: ".$_SERVER['PHP_SELF']."#reviews");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="landstyle.css">
    <title>TripOps</title>
</head>
<body>

<!-- ================= HERO ================= -->
<section class="hero">
    <div class="header" style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px;">
        <div class="logo">TripOps</div>
        <div class="nav-btns" style="display:flex; align-items:center; gap:15px;">

            <?php if(isset($_SESSION["user"])): ?>
                <!-- User Profile Dropdown -->
                <div class="user-profile" onclick="toggleDropdown()">
                    <div class="profile-circle">
                        <?php
                        if(!empty($_SESSION["profile_pic"]) && file_exists($_SESSION["profile_pic"])) {
                            echo '<img src="'.$_SESSION["profile_pic"].'?t='.time().'">';
                        } else {
                            echo strtoupper(substr($_SESSION["user"],0,1));
                        }
                        ?>
                    </div>
                </div>

                <div class="profile-dropdown" id="profileDropdown">
                    <div class="dropdown-header">Hi, <span><?= htmlspecialchars($_SESSION["user"]) ?></span>!</div>

                    <form method="POST" enctype="multipart/form-data" style="margin:0;">
                        <input type="file" name="profile_image" id="profileUpload" style="display:none;" onchange="uploadProfile(this)">
                        <a href="#" onclick="document.getElementById('profileUpload').click(); return false;">Change Profile Picture</a>
                    </form>

                    <a href="#" onclick="openPasswordModal()">Change Password</a>

                    <form method="POST" style="margin:0;">
                        <button name="logout">Logout</button>
                    </form>
                </div>

            <?php else: ?>
                <button class="login-btn">Login</button>
                <button class="signup">Sign Up</button>
            <?php endif; ?>

        </div>
    </div>

    <div class="hero-content">
        <p>Plan smarter. Travel better.</p>
        <h1>Your Journey, Perfectly Organized</h1>
        <?php if(!isset($_SESSION["user"])): ?>
            <button class="get-started login-btn">Start Planning</button>
        <?php else: ?>
            <a href="destination.php"><button class="get-started">Start Planning</button></a>
        <?php endif; ?>
    </div>
</section>


<!-- ================= FEATURED BLOGS ================= -->
<section class="featured-blogs">
    <h2>Travel Stories & Tips</h2>
    <p class="section-sub">Explore experiences shared by travelers and expert guides</p>

    <div class="blog-grid">
        <div class="blog-card">
            <img src="blog1.jpg" alt="Top Destinations in India">
            <div class="blog-content">
                <h4>Top 10 Destinations in India</h4>
                <p>Discover the most breathtaking places across India to plan your next adventure.</p>
                <a href="https://www.touropia.com/best-places-to-visit-in-india/" class="read-more">Read More</a>
            </div>
        </div>

        <div class="blog-card">
            <img src="blog2.jpg" alt="Best Beaches">
            <div class="blog-content">
                <h4>16 Tips to Plan Your Trip</h4>
                <p>Discover 16 essential tips to plan your trip like a pro—covering everything from budgeting and packing to choosing destinations and creating the perfect itinerary.</p>
                <a href="https://www.nomadicmatt.com/travel-blogs/planning-a-trip/" class="read-more">Read More</a>
            </div>
        </div>

        <div class="blog-card">
            <img src="blog3.jpg" alt="Adventurous Trips">
            <div class="blog-content">
                <h4>Adventurous Trips for Thrill Seekers</h4>
                <p>Get your adrenaline pumping with these exciting adventure destinations.</p>
                <a href="https://www.thebrokebackpacker.com/best-places-for-adventure-travel/" class="read-more">Read More</a>
            </div>
        </div>
    </div>
</section>

<!-- ================= FAQ ACCORDION ================= -->
<section class="faq-section">
    <h2>Frequently Asked Questions</h2>
    <p class="section-sub">Answers to common queries about using TripOps</p>

    <div class="faq-accordion">
        <div class="faq-item">
            <button class="faq-question">How do I plan a trip with TripOps?</button>
            <div class="faq-answer">
                <p>Simply select your destination, dates, and preferences. TripOps will guide you step by step.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">Is TripOps free to use?</button>
            <div class="faq-answer">
                <p>Yes! All of our features are free.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question">Can I collaborate with friends?</button>
            <div class="faq-answer">
                <p>Absolutely! You can plan trips together, split expenses, and share itineraries easily.</p>
            </div>
        </div>
    </div>
</section>

<!-- ================= WHY CHOOSE ================= -->
<section class="why-tripops">
    <h2>Why Choose TripOps?</h2>
    <p class="section-sub">One platform. Every travel need.</p>

    <div class="cards">
        <div class="card"><div class="card-icon">🌍</div><h4>Destination Insights</h4><p>Weather, culture & food.</p></div>
        <div class="card"><div class="card-icon">🧳</div><h4>Smart Packing</h4><p>Climate-based suggestions.</p></div>
        <div class="card"><div class="card-icon">👥</div><h4>Group Planning</h4><p>Collaborate easily.</p></div>
        <div class="card"><div class="card-icon">💰</div><h4>Expense Split</h4><p>Transparent tracking.</p></div>
        <div class="card"><div class="card-icon">🛟</div><h4>Safety Support</h4><p>Emergency help.</p></div>
        <div class="card"><div class="card-icon">🎯</div><h4>Mood-Based Trips</h4><p>Personalized journeys.</p></div>
        <div class="card"><div class="card-icon">📋</div><h4>Backup Plans</h4><p>Handle changes.</p></div>
        <div class="card"><div class="card-icon">📄</div><h4>Download Plans</h4><p>PDF itineraries.</p></div>
    </div>
</section>

<!-- ================= REVIEWS ================= -->
<section class="reviews" id="reviews">
    <h2>Traveler Reviews</h2>
    <p class="section-sub">Rated by real users who planned smarter with TripOps</p>

    <div class="review-grid">
        <?php
        $res = $conn->query("
            SELECT r.id AS review_id, r.user_id, r.rating, r.review, u.name, u.profile_pic 
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            ORDER BY r.created_at DESC
        ");
        while($r = $res->fetch_assoc()):
            $avatar = $r['profile_pic'] ? $r['profile_pic']."?t=".time() : ''; // prevent caching
        ?>
        <div class="review-card">
            <div class="review-header">
                <div class="avatar">
                    <?php if($avatar): ?>
                        <img src="<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($r['name']) ?>" style="width:100%;height:100%;border-radius:50%;">
                    <?php else: ?>
                        <?= strtoupper(substr($r['name'],0,1)) ?>
                    <?php endif; ?>
                </div>
                <div>
                    <strong><?= htmlspecialchars($r['name']) ?></strong>
                    <div class="stars"><?= str_repeat('★',$r['rating']) . str_repeat('☆', 5-$r['rating']) ?></div>
                </div>
            </div>
            <?php if($r['review']): ?>
                <p><?= htmlspecialchars($r['review']) ?></p>
            <?php endif; ?>

            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $r['user_id']): ?>
                <form method="POST" style="margin-top:10px;">
                    <input type="hidden" name="review_id" value="<?= $r['review_id'] ?>">
                    <button name="delete_review" style="padding:6px 12px;font-size:0.9rem;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;">Delete</button>
                </form>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
</section>

<!-- ================= ADD REVIEW ================= -->
<?php if (isset($_SESSION["user"])): ?>
<section class="add-review">
    <h2>Add Your Review</h2>
    <p class="section-sub">Share your experience with TripOps</p>

    <form method="POST" class="review-form">
        <div class="review-stars">
            <input type="radio" id="star5" name="rating" value="5" required><label for="star5">★</label>
            <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
            <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
            <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
            <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
        </div>
        <textarea name="review" placeholder="Write your review (optional)"></textarea>
        <button type="submit" name="submit_review">Post Review</button>
    </form>
</section>
<?php endif; ?>

<!-- ================= FOOTER ================= -->
<footer class="footer">
    <div class="footer-grid">
        <div><h4>TripOps</h4><p>Your ultimate travel partner.</p></div>
        <div><h4>Company</h4><p>About Us<br>Careers</p></div>
        <div><h4>Support</h4><p>Help Center<br>Contact</p></div>
        <div><h4>Follow Us</h4><p>Instagram<br>Twitter</p></div>
    </div>
</footer>

<!-- ================= MODALS ================= -->
<!-- LOGIN MODAL -->
<div class="modal" id="loginModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('loginModal')">&times;</span>
        <h2>Login</h2>
        <form method="POST" id="loginForm">
            <input type="email" name="email" required placeholder="Email">
            <input type="password" name="password" required placeholder="Password">
            <button name="login">Login</button>
        </form>
        <p class="modal-message" id="loginMessage" style="color:red;"><?= isset($message) && isset($_POST["login"]) ? htmlspecialchars($message) : '' ?></p>
        <p class="switch">Don't have an account? <a onclick="switchModal('signupModal','loginModal')">Sign Up</a></p>
    </div>
</div>

<!-- SIGNUP MODAL -->
<div class="modal" id="signupModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('signupModal')">&times;</span>
        <h2>Sign Up</h2>
        <form method="POST" id="signupForm">
            <input type="text" name="name" required placeholder="Full Name">
            <input type="email" name="email" required placeholder="Email">
            <input type="password" name="password" required placeholder="Create a Password">
            <button name="signup">Sign Up</button>
        </form>
        <p class="modal-message" id="signupMessage" style="color:red;"><?= isset($message) && isset($_POST["signup"]) ? htmlspecialchars($message) : '' ?></p>
        <p class="switch">Already have an account? <a onclick="switchModal('loginModal','signupModal')">Login</a></p>
    </div>
</div>

<!-- CHANGE PASSWORD MODAL -->
<div class="modal" id="changePasswordModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('changePasswordModal')">&times;</span>
        <h2>Change Password</h2>
        <form method="POST" id="changePasswordForm">
            <input type="password" name="current_password" required placeholder="Current Password">
            <input type="password" name="new_password" required placeholder="New Password">
            <button name="change_password">Change Password</button>
        </form>
        <p class="modal-message" id="changePasswordMessage" style="color:green;"><?= isset($message) && isset($_POST["change_password"]) ? htmlspecialchars($message) : '' ?></p>
    </div>
</div>

<!-- ================= JS ================= -->
<script>
// Slider
let index = 0;
function slide(dir){
    const slider = document.getElementById("slider");
    const cardWidth = 280;
    const visible = Math.floor(slider.parentElement.offsetWidth / cardWidth);
    const max = slider.children.length - visible;
    index += dir;
    index = Math.max(0, Math.min(index, max));
    slider.style.transform = `translateX(${-index * cardWidth}px)`;
}

// Modal handlers
function closeModal(id){
    document.getElementById(id).style.display='none';
    if(id==='changePasswordModal') document.getElementById('changePasswordMessage').innerHTML='';
    if(id==='loginModal') document.getElementById('loginMessage').innerHTML='';
    if(id==='signupModal') document.getElementById('signupMessage').innerHTML='';
}

function switchModal(show,hide){
    closeModal(hide);
    document.getElementById(show).style.display='flex';
}

document.querySelectorAll('.login-btn').forEach(btn=>{ btn.onclick=()=>document.getElementById('loginModal').style.display='flex'; });
document.querySelector('.signup')?.addEventListener('click',()=>{ document.getElementById('signupModal').style.display='flex'; });

// Profile dropdown
function toggleDropdown(){
    document.getElementById("profileDropdown").classList.toggle('show');
}
window.onclick = function(e){
    if(!e.target.closest('.user-profile')) document.getElementById("profileDropdown").classList.remove('show');
}
function openPasswordModal(){
    document.getElementById('changePasswordMessage').innerHTML='';
    closeModal('loginModal');
    closeModal('signupModal');
    document.getElementById("profileDropdown").classList.remove('show');
    document.getElementById("changePasswordModal").style.display='flex';
}

// Profile pic upload
function uploadProfile(input){
    const file = input.files[0];
    if(file){
        const formData = new FormData();
        formData.append('profile_image', file);

        fetch('<?= $_SERVER['PHP_SELF'] ?>', { method:'POST', body:formData })
        .then(res => res.text())
        .then(()=>{
            const reader = new FileReader();
            reader.onload = function(e){
                const profileCircle = document.querySelector('.profile-circle');
                let img = profileCircle.querySelector('img');
                if(!img){
                    img = document.createElement('img');
                    img.style.width="100%"; img.style.height="100%"; img.style.objectFit="cover";
                    profileCircle.appendChild(img);
                }
                img.src = e.target.result;
            }
            reader.readAsDataURL(file);
        });
    }
}

// FAQ accordion
document.querySelectorAll('.faq-question').forEach(item=>{
    item.addEventListener('click',()=>{
        item.parentElement.classList.toggle('active');
    });
});

// Open modals on server-side flags
document.addEventListener("DOMContentLoaded", function () {
    <?php if(isset($openLoginModal)): ?> document.getElementById('loginModal').style.display='flex'; <?php endif; ?>
    <?php if(isset($openSignupModal)): ?> document.getElementById('signupModal').style.display='flex'; <?php endif; ?>
    <?php if(isset($passwordSuccess) || isset($passwordError)): ?> document.getElementById('changePasswordModal').style.display='flex'; <?php endif; ?>
});

// Smooth scroll if URL has #reviews
document.addEventListener("DOMContentLoaded", () => {
    if(window.location.hash === "#reviews") {
        const reviewsSection = document.getElementById("reviews");
        if(reviewsSection) {
            reviewsSection.scrollIntoView({ behavior: "smooth" });
        }
    }
});

// Auto-close password modal on success
<?php if(isset($passwordSuccess)): ?>
setTimeout(()=>{
    const modal = document.getElementById('changePasswordModal');
    modal.style.display='none';
    document.getElementById('changePasswordMessage').innerHTML='';
},2000);
<?php endif; ?>
</script>

</body>
</html>
