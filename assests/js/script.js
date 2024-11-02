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
