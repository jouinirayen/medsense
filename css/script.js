/**
 * Scripts généraux du site
 */

// Confirmation avant suppression
function confirmDelete(message = "Êtes-vous sûr de vouloir supprimer cet élément ?") {
 return confirm(message);
}

// Fermer les messages après 5 secondes
document.addEventListener('DOMContentLoaded', function () {
 const messages = document.querySelectorAll('.message');
 messages.forEach(msg => {
  setTimeout(() => {
   msg.style.opacity = '0';
   msg.style.transition = 'opacity 0.5s';
   setTimeout(() => {
    msg.style.display = 'none';
   }, 500);
  }, 5000);
 });
});
