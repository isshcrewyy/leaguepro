// JavaScript for switching tabs
document.addEventListener("DOMContentLoaded", function () {
    const addPlayerTab = document.getElementById("addPlayerTab");
    const addCoachTab = document.getElementById("addCoachTab");
    const playerForm = document.getElementById("playerForm");
    const coachForm = document.getElementById("coachForm");

    // Add click event to "Add Player" tab
    addPlayerTab.addEventListener("click", function () {
        // Show player form and hide coach form
        playerForm.style.display = "block";
        coachForm.style.display = "none";

        // Set active tab
        addPlayerTab.classList.add("active");
        addCoachTab.classList.remove("active");
    });

    // Add click event to "Add Coach" tab
    addCoachTab.addEventListener("click", function () {
        // Show coach form and hide player form
        coachForm.style.display = "block";
        playerForm.style.display = "none";

        // Set active tab
        addCoachTab.classList.add("active");
        addPlayerTab.classList.remove("active");
    });

    // Initialize default view (show player form by default)
    addPlayerTab.click();
});
function toggleMenu() {
    const navLinks = document.getElementById('nav-links');
    navLinks.classList.toggle('show'); // Toggle the "show" class
}
