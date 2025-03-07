let name= "";
// JavaScript for switching tabs
document.addEventListener("DOMContentLoaded", function () {
    const addPlayerTab = document.getElementById("addPlayerTabBtn");
    const addCoachTab = document.getElementById("addCoachTabBtn");
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

function toggleEdit(button, type, id) {
    const row = document.getElementById(`${type}_${id}`);
    const displayValues = row.getElementsByClassName('display-value');
    const editInputs = row.getElementsByClassName('edit-input');
    const editBtn = row.getElementsByClassName('edit-btn')[0];
    const saveBtn = row.getElementsByClassName('save-btn')[0];
    const cancelBtn = row.getElementsByClassName('cancel-btn')[0];

    // Hide display values, show edit inputs
    Array.from(displayValues).forEach(span => span.style.display = 'none');
    Array.from(editInputs).forEach(input => input.style.display = 'inline-block');

    // Toggle buttons
    editBtn.style.display = 'none';
    saveBtn.style.display = 'inline-block';
    cancelBtn.style.display = 'inline-block';
}

function cancelEdit(button, type, id) {
    const row = document.getElementById(`${type}_${id}`);
    const displayValues = row.getElementsByClassName('display-value');
    const editInputs = row.getElementsByClassName('edit-input');
    const editBtn = row.getElementsByClassName('edit-btn')[0];
    const saveBtn = row.getElementsByClassName('save-btn')[0];
    const cancelBtn = row.getElementsByClassName('cancel-btn')[0];

    // Show display values, hide edit inputs
    Array.from(displayValues).forEach(span => span.style.display = 'inline');
    Array.from(editInputs).forEach(input => input.style.display = 'none');

    // Reset input values to original
    Array.from(editInputs).forEach((input, index) => {
        input.value = displayValues[index].textContent;
    });

    // Toggle buttons
    editBtn.style.display = 'inline-block';
    saveBtn.style.display = 'none';
    cancelBtn.style.display = 'none';
}

function saveChanges(button, type, id) {
    const row = document.getElementById(`${type}_${id}`);
    const editInputs = row.getElementsByClassName('edit-input');
    const values = Array.from(editInputs).map(input => input.value);

    // Create FormData object
    const formData = new FormData();
    formData.append('id', id);
    formData.append('type', type);
    formData.append('name', values[0]);
    formData.append('age', values[1]);
    formData.append(type === 'player' ? 'position' : 'experience', values[2]);
    formData.append('club_id', values[3]);
    formData.append('phone_number', values[4]);
    p_name = values[0];
    // Send AJAX request
    fetch('update_team.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update display values
            const displayValues = row.getElementsByClassName('display-value');
            
            Array.from(displayValues).forEach((span, index) => {
                span.textContent = values[index];
            });
            // Reset display
            cancelEdit(button, type, id);
            alert('Updated !');
            setTimeout(() => window.location.reload(), 500); // Ensure page reload
            
           
        } else {
            alert('Error updating: ' + data.message);
        }
        
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating record');
    });
}