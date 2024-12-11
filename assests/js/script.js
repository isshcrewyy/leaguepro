function validateForm(form) {
    const coachName = form.coach_name.value;
    const age = form.age.value;
    const clubId = form.club_id.value;
    const experience = form.experience.value;

    if (!coachName || !age || !clubId || !experience) {
        alert("Please fill in all fields.");
        return false;
    }
    return true;
}

function confirmDelete() {
    return confirm("Are you sure you want to delete this item?");
}

function showModal() {
    document.getElementById('leagueModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('leagueModal').style.display = 'none';
}

// Close modal if clicked outside of it
window.onclick = function (event) {
    const modal = document.getElementById('leagueModal');
    if (event.target === modal) {
        closeModal();
    }
};
