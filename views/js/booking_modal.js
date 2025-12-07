/**
 * Handles the opening of the booking modal with SweetAlert.
 * Relies on global variables: window.currentUser, window.doctorId
 */
function openBookingModal(time, date) {
    const currentUser = window.currentUser || {};
    const prefillNom = currentUser.nom || '';
    const prefillPrenom = currentUser.prenom || '';

    Swal.fire({
        title: '<span class="modal-title-custom">Confirmer le rendez-vous</span>',
        html: `
            <div class="modal-summary-card">
                <div class="summary-row mb-2">
                    <span class="summary-label"><i class="far fa-calendar-alt"></i> Date</span>
                    <span class="summary-value-date">${date}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label"><i class="far fa-clock"></i> Heure</span>
                    <span class="summary-value-time">${time}</span>
                </div>
            </div>
            
            <form id="booking-form" action="book.php" method="POST" class="booking-form">
                <input type="hidden" name="doctor_id" value="${window.doctorId}">
                <input type="hidden" name="date" value="${date}">
                <input type="hidden" name="slot_time" value="${time}">

                <label class="section-label">Vos coordonnées</label>
                
                <div class="input-group">
                    <i class="fas fa-user input-icon"></i>
                    <input name="nom" id="swal-nom" class="swal2-input input-field-custom" placeholder="Votre Nom" value="${prefillNom}">
                </div>

                <div class="input-group last">
                     <i class="fas fa-user-tag input-icon"></i>
                    <input name="prenom" id="swal-prenom" class="swal2-input input-field-custom" placeholder="Votre Prénom" value="${prefillPrenom}">
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Confirmer <i class="fas fa-arrow-right" style="margin-left: 5px;"></i>',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#0ea5e9',
        cancelButtonColor: '#64748b',
        customClass: {
            popup: 'swal-rounded-popup',
            confirmButton: 'swal-btn-confirm',
            cancelButton: 'swal-btn-cancel'
        },
        preConfirm: () => {
            const nom = document.getElementById('swal-nom').value;
            const prenom = document.getElementById('swal-prenom').value;

            if (!nom || !prenom) {
                Swal.showValidationMessage('Veuillez remplir tous les champs');
                return false;
            }

            // Submit the form programmatically
            document.getElementById('booking-form').submit();
            return false; // Prevent modal from closing immediately
        }
    });
}
