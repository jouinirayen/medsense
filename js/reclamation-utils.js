/**
 * Système de notifications et validations pour les réclamations
 * Fonctions réutilisables pour popups, alertes et validations
 */

// ============================================
// SYSTÈME DE POPUP/ALERTE
// ============================================

/**
 * Affiche une popup de confirmation personnalisée
 * @param {string} message - Message à afficher
 * @param {string} title - Titre de la popup
 * @returns {Promise<boolean>} - true si confirmé, false si annulé
 */
function showConfirmPopup(message, title = 'Confirmation') {
    return new Promise((resolve) => {
        // Créer l'overlay
        const overlay = document.createElement('div');
        overlay.id = 'confirmOverlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            animation: fadeIn 0.3s ease;
        `;

        // Créer la popup
        const popup = document.createElement('div');
        popup.style.cssText = `
            background: white;
            border-radius: 12px;
            padding: 2rem;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease;
        `;

        popup.innerHTML = `
            <div style="text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">⚠️</div>
                <h3 style="margin: 0 0 1rem 0; color: #1e293b; font-size: 1.3rem;">${title}</h3>
                <p style="margin: 0 0 2rem 0; color: #64748b; line-height: 1.6;">${message}</p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button id="confirmBtn" style="
                        background: #ef4444;
                        color: white;
                        border: none;
                        padding: 10px 24px;
                        border-radius: 8px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.3s ease;
                    ">Oui, supprimer</button>
                    <button id="cancelBtn" style="
                        background: #6b7280;
                        color: white;
                        border: none;
                        padding: 10px 24px;
                        border-radius: 8px;
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.3s ease;
                    ">Annuler</button>
                </div>
            </div>
        `;

        overlay.appendChild(popup);
        document.body.appendChild(overlay);

        // Ajouter les styles d'animation
        if (!document.getElementById('popupStyles')) {
            const style = document.createElement('style');
            style.id = 'popupStyles';
            style.textContent = `
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                @keyframes slideUp {
                    from { transform: translateY(20px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
                #confirmBtn:hover {
                    background: #dc2626;
                    transform: translateY(-2px);
                }
                #cancelBtn:hover {
                    background: #4b5563;
                    transform: translateY(-2px);
                }
            `;
            document.head.appendChild(style);
        }

        // Gestionnaires d'événements
        const confirmBtn = popup.querySelector('#confirmBtn');
        const cancelBtn = popup.querySelector('#cancelBtn');

        confirmBtn.addEventListener('click', () => {
            document.body.removeChild(overlay);
            resolve(true);
        });

        cancelBtn.addEventListener('click', () => {
            document.body.removeChild(overlay);
            resolve(false);
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                document.body.removeChild(overlay);
                resolve(false);
            }
        });
    });
}

/**
 * Affiche une alerte de succès/erreur
 * @param {string} message - Message à afficher
 * @param {string} type - Type d'alerte: 'success', 'error', 'warning'
 */
function showAlert(message, type = 'success') {
    const container = document.getElementById('alertContainer') || createAlertContainer();
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#f59e0b'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 10001;
        min-width: 300px;
        max-width: 500px;
        animation: slideInRight 0.3s ease;
        display: flex;
        align-items: center;
        gap: 1rem;
    `;

    const icons = {
        success: '✓',
        error: '✗',
        warning: '⚠'
    };

    alert.innerHTML = `
        <span style="font-size: 1.5rem; font-weight: bold;">${icons[type] || 'ℹ'}</span>
        <span style="flex: 1;">${message}</span>
        <button onclick="this.parentElement.remove()" style="
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        ">×</button>
    `;

    container.appendChild(alert);

    // Ajouter l'animation CSS si nécessaire
    if (!document.getElementById('alertStyles')) {
        const style = document.createElement('style');
        style.id = 'alertStyles';
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(400px); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(400px); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }

    // Auto-remove après 5 secondes
    setTimeout(() => {
        alert.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (alert.parentElement) {
                alert.remove();
            }
        }, 300);
    }, 5000);
}

/**
 * Crée le conteneur pour les alertes si il n'existe pas
 */
function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alertContainer';
    document.body.appendChild(container);
    return container;
}

// ============================================
// VALIDATION DE FORMULAIRE
// ============================================

/**
 * Valide le formulaire de réclamation
 * @param {HTMLFormElement} form - Le formulaire à valider
 * @returns {boolean} - true si valide, false sinon
 */
function validateReclamationForm(form) {
    const titre = form.querySelector('[name="titre"]');
    const description = form.querySelector('[name="description"]');
    
    let isValid = true;
    const errors = [];

    // Validation du titre
    if (!titre || !titre.value.trim()) {
        errors.push('Le titre est requis.');
        isValid = false;
        if (titre) {
            titre.style.borderColor = '#ef4444';
        }
    } else if (titre.value.trim().length < 3) {
        errors.push('Le titre doit contenir au moins 3 caractères.');
        isValid = false;
        titre.style.borderColor = '#ef4444';
    } else if (titre.value.trim().length > 255) {
        errors.push('Le titre ne doit pas dépasser 255 caractères.');
        isValid = false;
        titre.style.borderColor = '#ef4444';
    } else if (titre) {
        titre.style.borderColor = '#10b981';
    }

    // Validation de la description
    if (!description || !description.value.trim()) {
        errors.push('La description est requise.');
        isValid = false;
        if (description) {
            description.style.borderColor = '#ef4444';
        }
    } else if (description.value.trim().length < 10) {
        errors.push('La description doit contenir au moins 10 caractères.');
        isValid = false;
        description.style.borderColor = '#ef4444';
    } else if (description.value.trim().length > 5000) {
        errors.push('La description ne doit pas dépasser 5000 caractères.');
        isValid = false;
        description.style.borderColor = '#ef4444';
    } else if (description) {
        description.style.borderColor = '#10b981';
    }

    // Afficher les erreurs
    if (!isValid && errors.length > 0) {
        showAlert(errors.join('<br>'), 'error');
    }

    return isValid;
}

/**
 * Initialise la validation en temps réel pour un formulaire
 * @param {HTMLFormElement} form - Le formulaire à valider
 */
function initRealtimeValidation(form) {
    const titre = form.querySelector('[name="titre"]');
    const description = form.querySelector('[name="description"]');

    if (titre) {
        titre.addEventListener('input', function() {
            const value = this.value.trim();
            if (value.length >= 3 && value.length <= 255) {
                this.style.borderColor = '#10b981';
            } else {
                this.style.borderColor = '#e5e7eb';
            }
        });
    }

    if (description) {
        description.addEventListener('input', function() {
            const value = this.value.trim();
            if (value.length >= 10 && value.length <= 5000) {
                this.style.borderColor = '#10b981';
            } else {
                this.style.borderColor = '#e5e7eb';
            }
        });
    }
}

// ============================================
// GESTION DES LIENS DE SUPPRESSION
// ============================================

/**
 * Initialise les confirmations de suppression pour tous les liens de suppression
 */
function initDeleteConfirmations() {
    document.querySelectorAll('a[href*="delete"], a[href*="supprimer"]').forEach(link => {
        link.addEventListener('click', async function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            const confirmed = await showConfirmPopup(
                'Êtes-vous sûr de vouloir supprimer cette réclamation ?<br><strong>Cette action est irréversible.</strong>',
                'Confirmer la suppression'
            );
            
            if (confirmed) {
                window.location.href = url;
            }
        });
    });
}

// Initialiser au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    initDeleteConfirmations();
    createAlertContainer();
});


