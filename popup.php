<!DOCTYPE html>
<html>
    <body>
<div id="travel-hack-popup">
    <div class="popup-header">
        <span class="popup-icon">💡</span>
        <span class="popup-title">Travel Tip</span>
        <span class="popup-close" onclick="closeTravelPopup()">&times;</span>
    </div>
    <div class="popup-body">
        <p id="hack-text">Rolling your clothes saves 30% more space!</p>
    </div>
    <div class="popup-progress"></div>
</div>
<!-- ================= FOOTER ================= -->
<footer class="footer">
    <div class="footer-grid">
        <div><h4>TripOps</h4><p>Your ultimate travel partner.</p></div>
        <div><h4>Company</h4><p>About Us<br>Careers</p></div>
        <div><h4>Support</h4><p>Help Center<br>Contact</p></div>
        <div><h4>Follow Us</h4><p>Instagram<br>Twitter</p></div>
    </div>
</footer>
<style>
    #travel-hack-popup {
        position: fixed;
        bottom: 25px;
        right: 25px;
        width: 320px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        z-index: 9999;
        display: none; /* Hidden by default */
        overflow: hidden;
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        border: 1px solid #e0e0e0;
        animation: popupSlideIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .popup-header {
        background: #f8f9fa;
        padding: 12px 15px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid #eee;
    }

    .popup-icon { font-size: 18px; margin-right: 10px; }
    
    .popup-title {
        flex-grow: 1;
        font-weight: 700;
        color: #333;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .popup-close {
        cursor: pointer;
        font-size: 22px;
        color: #999;
        line-height: 20px;
        transition: color 0.2s;
    }

    .popup-close:hover { color: #333; }

    .popup-body { padding: 15px; }

    #hack-text {
        margin: 0;
        font-size: 15px;
        color: #555;
        line-height: 1.5;
    }

    /* Progress bar that shrinks while popup is visible */
    .popup-progress {
        height: 4px;
        background: #007bff;
        width: 100%;
    }
/* ================= FOOTER ================= */
.footer {
    background: #2f5d7c;
    color: white;
    padding: 40px 10%;
    margin-top: 60px;
}
.footer-grid {
    display: grid;
    grid-template-columns: repeat(4,1fr);
    gap: 20px;
}
.footer h4 {
    margin-bottom: 10px;
}
    @keyframes popupSlideIn {
        from { transform: translateY(100px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes progressBar {
        from { width: 100%; }
        to { width: 0%; }
    }

    .animate-progress {
        animation: progressBar 12s linear forwards;
    }
</style>

<script>
    const allHacks = [
        "Use 'Incognito Mode' when booking flights to avoid price hikes.",
        "Roll your clothes instead of folding to save 30% suitcase space.",
        "Download Google Maps for offline use before you arrive in a new city.",
        "Keep a photo of your passport and visa in your email or cloud storage.",
        "Carry an empty water bottle through security and fill it at a fountain.",
        "Always pack a portable power bank—navigation drains battery quickly.",
        "Use local ATMs instead of airport exchange kiosks for better rates.",
        "Email your itinerary to a family member for safety.",
        "Pack a small first-aid kit with basic meds like aspirin and plasters.",
        "Learn 'Hello' and 'Thank You' in the local language of your destination."
    ];

    function showTravelPopup() {
        const popup = document.getElementById('travel-hack-popup');
        const textDisplay = document.getElementById('hack-text');
        const progressBar = document.querySelector('.popup-progress');

        // 1. Pick a random hack
        const randomItem = allHacks[Math.floor(Math.random() * allHacks.length)];
        textDisplay.innerText = randomItem;

        // 2. Show the popup
        popup.style.display = 'block';
        
        // 3. Start the progress bar animation (12 seconds)
        progressBar.classList.remove('animate-progress');
        void progressBar.offsetWidth; // Trigger reflow to restart animation
        progressBar.classList.add('animate-progress');

        // 4. Automatically hide after 12 seconds
        setTimeout(closeTravelPopup, 12000);
    }

    function closeTravelPopup() {
        document.getElementById('travel-hack-popup').style.display = 'none';
    }

    // --- TRIGGER LOGIC ---

    // Initial trigger: Show the first hack 10 seconds after landing
    setTimeout(showTravelPopup, 10000);

    // Recurring trigger: Show a hack every 2 minutes (120,000ms)
    setInterval(showTravelPopup, 120000);
</script>
</body>
</html>