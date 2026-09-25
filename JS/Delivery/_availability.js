// ============================================================ */
// AVAILABILITY TOGGLE
// ============================================================ */
const toggleSwitch = document.querySelector('#availabilityToggle input[type="checkbox"]');
const toggleStatus = document.getElementById('availStatus');

if (toggleSwitch && toggleStatus) {
    toggleSwitch.addEventListener('change', function() {
        if (this.checked) {
            toggleStatus.textContent = 'Available';
            toggleStatus.style.color = 'var(--md-primary)';
        } else {
            toggleStatus.textContent = 'Unavailable';
            toggleStatus.style.color = 'var(--md-error)';
        }
    });
}