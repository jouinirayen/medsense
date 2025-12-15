
<?php
session_start();

// Redirection si pas connecté
if (!isset($_SESSION['user_id']) || !isset($_SESSION['prenom']) || !isset($_SESSION['nom'])) {
    header('Location: login.php');
    exit;
}

// Avatar fixe bleu (SVG)
$avatarSVG = '<svg width="100%" height="100%" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
    <circle cx="50" cy="50" r="50" fill="#374151"/>
    <circle cx="50" cy="35" r="18" fill="#9ca3af"/>
</svg>';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>medsense - Nouveau post</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fff 0%, #f0f9ff 50%, #e0f2fe 100%);
            background-attachment: fixed;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        body::before {
            content: ''; position: fixed; inset: 0; pointer-events: none; z-index: 0;
            background:
                radial-gradient(circle at 20% 50%, rgba(147,197,253,.08), transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(103,232,249,.06), transparent 50%);
        }
        .header {
            background: rgba(255,255,255,.95);
            backdrop-filter: blur(20px);
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 2px 20px rgba(0,0,0,.04);
            border-bottom: 1px solid rgba(147,197,253,.2);
        }
        .logo img { width: 200px; }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .user-details {
            text-align: right;
            line-height: 1.4;
        }
        .user-details small { 
            color: #64748b; 
            font-size: 13px; 
            display: block; 
        }
        .user-details strong { 
            font-size: 18px; 
            color: #1e40af; 
            font-weight: 600;
        }
        
        .avatar {
            width: 44px; height: 44px; border-radius: 50%;
            border: 2px solid #3b82f6; /* Correction: enlevé .monitor5px qui était une faute de frappe */
            box-shadow: 0 2px 8px rgba(59,130,246,.2);
            transition: transform .2s;
            overflow: hidden;
            background: #2563eb;
            flex-shrink: 0;
        }
        .avatar:hover { transform: scale(1.05); }
        .main-content { flex: 1; padding: 20px; }

        .post-box {
            display: flex; flex-direction: column; background: white;
            padding: 20px; border-radius: 24px;
            box-shadow: 0 -8px 32px rgba(0,0,0,.08), 0 -4px 16px rgba(0,0,0,.06);
            border: 1px solid rgba(226,232,240,.9);
            width: 100%; max-width: 620px; margin: 0 auto 30px;
            gap: 12px;
        }
        .main-post-line { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
        }
        .input-wrapper {
            flex: 1; 
            position: relative;
        }
        .input {
            width: 100%; 
            padding: 14px 80px 14px 20px; /* ← espace pour les deux icônes */
            border: none;
            background: rgba(248,250,252,.95); border-radius: 30px;
            font: 500 16px/1 'Inter', sans-serif; color: #1e293b; outline: none;
            height: 48px;
        }
        .input:focus { background: white; box-shadow: inset 0 1px 3px rgba(0,0,0,.1), 0 0 0 3px rgba(59,130,246,.15); }

        /* Les deux icônes à l’intérieur du champ */
        .media-icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #64748b;
            font-size: 18px;
            transition: all .2s;
        }
        .media-icon:hover { color: #3b82f6; background: rgba(59,130,246,.1); border-radius: 50%; }

        /* Positionnement précis */
        #iconImage { right: 52px; }   /* icône image */
        #iconRobot { right: 12px; }   /* icône robot (chatbot) */

        .btn {
            width: 48px; height: 48px; border: none; border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #2563eb); color: white;
            font-size: 19px; cursor: pointer; display: flex; align-items: center;
            justify-content: center; box-shadow: 0 6px 20px rgba(59,130,246,.35);
            transition: all .35s ease;
            flex-shrink: 0;
        }
        .btn:hover { transform: translateY(-4px) scale(1.1); box-shadow: 0 12px 30px rgba(59,130,246,.5); }

        .media-zone { display: flex; flex-wrap: wrap; gap: 12px; }
        .media-preview {
            position: relative; width: 120px; height: 120px; border-radius: 12px;
            overflow: hidden; border: 2px solid #e2e8f0;
        }
        .media-preview img { width: 100%; height: 100%; object-fit: cover; }
        .remove-media {
            position: absolute; top: 6px; right: 6px; width: 28px; height: 28px;
            background: rgba(0,0,0,0.6); color: white; border: none; border-radius: 50%;
            cursor: pointer; font-size: 14px; display: flex; align-items: center; justify-content: center;
        }
.textarea-auto {
    width: 100%;
    min-height: 52px;
    max-height: 400px;
    padding: 14px 80px 14px 20px !important;
    border: none;
    background: rgba(248,250,252,.95);
    border-radius: 30px;
    font: 500 16px/1.6 'Inter', sans-serif;
    color: #1e293b;
    outline: none;
    resize: none;
    overflow: hidden;
    transition: all 0.3s ease;
    field-sizing: content; /* Chrome 123+ – magique */
}

.textarea-auto:focus {
    background: white;
    min-height: 100px;
    box-shadow: inset 0 1px 3px rgba(0,0,0,.1), 0 0 0 3px rgba(59,130,246,.15);
}
    </style>
</head>
<body>

<header class="header">
    <div class="logo">
        <img src="http://localhost/blog/logo.png" alt="logo">
    </div>
    
    <div class="user-info">
        <div class="user-details">
            <small>Connecté en tant que</small>
            <strong>
                <?= htmlspecialchars($_SESSION['prenom'] . " " . $_SESSION['nom']) ?>
            </strong>
        </div>
        <div class="avatar"><?= $avatarSVG ?></div>
        
        <a href="logout.php" style="background:#dc2626;color:white;padding:11px 22px;border-radius:12px;text-decoration:none;font-weight:600;box-shadow:0 4px 12px rgba(220,38,38,0.3); transition: all 0.2s;"
           onmouseover="this.style.boxShadow='0 8px 20px rgba(220,38,38,0.5)'" 
           onmouseout="this.style.boxShadow='0 4px 12px rgba(220,38,38,0.3)'">
            Déconnexion
        </a>
    </div>
</header>

<div class="main-content"></div>

<div class="post-box">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <h2 style="font-size: 20px; color: #1e40af; font-weight: 600;">Créer une publication</h2>
        <a href="liste.php" style="background:#e2e8f0;color:#64748b;padding:10px 20px;border-radius:12px;text-decoration:none;font-weight:600;font-size:14px;display:flex;align-items:center;gap:8px;">
            Retour au fil
        </a>
    </div>

    <form action="ajout.php" method="POST" enctype="multipart/form-data" id="postForm">
        
        <div class="main-post-line">
            <div class="avatar"><?= $avatarSVG ?></div>
            
            <div class="input-wrapper">
    <!-- ON REMPLACE L'INPUT PAR UNE TEXTAREA QUI GRANDIT TOUTE SEULE -->
    <textarea 
        name="contenu" 
        id="postInput" 
        class="input textarea-auto" 
        placeholder="Quoi de neuf, <?= htmlspecialchars($_SESSION['prenom']) ?> ?" 
        autocomplete="off"
        rows="1"></textarea>

    <input type="file" name="images[]" id="imageInput" accept="image/*" multiple style="display:none;">

    <!-- Icône Image -->
    <label for="imageInput" class="media-icon" id="iconImage" title="Ajouter une image">
        <i class="fas fa-image"></i>
    </label>

    <!-- Icône Chatbot -->
    <div class="media-icon" id="iconRobot" title="Assistant IA">
        <i class="fas fa-robot" style="color:#8b5cf6;"></i>
    </div>
</div>

            <button type="submit" class="btn" id="submitBtn">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>

        <div class="media-zone" id="mediaPreview"></div>
    </form>
</div>

<!-- Script AJAX pour le formulaire de post -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Variables
const fileInput = document.getElementById('imageInput');
const previewContainer = document.getElementById('mediaPreview');
const postForm = document.getElementById('postForm');
const submitBtn = document.getElementById('submitBtn');
const postInput = document.getElementById('postInput');

let selectedFiles = [];

// Aperçu des images
function updatePreview() {
    previewContainer.innerHTML = '';
    selectedFiles.forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'media-preview';
            div.innerHTML = `
                <img src="${e.target.result}" alt="preview">
                <button type="button" class="remove-media" data-name="${file.name}">
                    <i class="fas fa-times"></i>
                </button>
            `;
            previewContainer.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

fileInput.addEventListener('change', function() {
    Array.from(this.files).forEach(file => {
        if (file.type.startsWith('image/') && file.size <= 10 * 1024 * 1024) {
            if (!selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                selectedFiles.push(file);
            }
        }
    });
    updatePreview();
});

previewContainer.addEventListener('click', e => {
    if (e.target.closest('.remove-media')) {
        const name = e.target.closest('.remove-media').dataset.name;
        selectedFiles = selectedFiles.filter(f => f.name !== name);
        updatePreview();
    }
});

postForm.addEventListener('submit', function(e) {
    e.preventDefault();

    const texte = postInput.value.trim();

    if (texte === '' && selectedFiles.length === 0) {
        Swal.fire('Oups !', 'Écris quelque chose ou ajoute une image', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('contenu', texte);
    selectedFiles.forEach(file => formData.append('images[]', file)); // Correction: name="images[]" pour multiple

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('ajout.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.text())
    .then(response => {
        if (response.trim() === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Publié avec succès !',
                timer: 1500,
                showConfirmButton: false
            }).then(() => location.href = 'liste.php');
        } else {
            throw new Error(response);
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire('Erreur', 'Impossible de publier. Réessaie.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
    });
});
</script>

<!-- ====================== CHATBOT GEMINI (VERSION CORRIGÉE ET SÉCURISÉE) ====================== -->
<script>
// Création du widget une seule fois
if (!document.getElementById('gemini-chat-widget')) {
    const widget = document.createElement('div');
    widget.id = 'gemini-chat-widget';
    widget.innerHTML = `
    <div class="chat-window" id="geminiChatWindow">
        <div class="chat-header">
            <div class="chat-title"><i class="fas fa-robot"></i> Assistant IA</div>
            <button class="close-chat">×</button>
        </div>
        <div class="chat-body">
            <div class="message bot">Bonjour ! Comment puis-je vous aider ?</div>
        </div>
        <div class="chat-footer">
            <input type="text" placeholder="Votre message…" autocomplete="off">
            <button id="sendMsg"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>`;

    const style = document.createElement('style');
    style.textContent = `
    #gemini-chat-widget{position:fixed;bottom:20px;right:20px;z-index:10000;font-family:'Inter',sans-serif}
    #geminiChatWindow{width:380px;height:520px;background:#fff;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.2);display:none;flex-direction:column;overflow:hidden}
    .chat-header{background:linear-gradient(135deg,#8b5cf6,#a855f7);color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;font-weight:600}
    .close-chat{background:none;border:none;color:#fff;font-size:28px;cursor:pointer;width:40px;height:40px;border-radius:50%}
    .close-chat:hover{background:rgba(255,255,255,.2)}
    .chat-body{flex:1;padding:20px;overflow-y:auto;background:#f8fafc;display:flex;flex-direction:column;gap:12px}
    .message{max-width:80%;padding:12px 16px;border-radius:18px;line-height:1.5}
    .bot{align-self:flex-start;background:#fff;border:1px solid #e2e8f0}
    .user{align-self:flex-end;background:#e0e7ff;color:#1e40af}
    .chat-footer{padding:16px;background:#fff;border-top:1px solid #e2e8f0;display:flex;gap:10px}
    .chat-footer input{flex:1;padding:12px 16px;border:1px solid #cbd5e1;border-radius:30px;outline:none}
    .chat-footer input:focus{border-color:#8b5cf6}
    #sendMsg{width:44px;height:44px;background:#8b5cf6;color:#fff;border:none;border-radius:50%;cursor:pointer;font-size:18px}
    `;
    document.head.appendChild(style);
    document.body.appendChild(widget);

    // Événements du chat
    const chatWin = document.getElementById('geminiChatWindow');
    const closeBtn = widget.querySelector('.close-chat');
    const input = widget.querySelector('.chat-footer input');
    const sendBtn = widget.querySelector('#sendMsg');

    closeBtn.onclick = () => chatWin.style.display = 'none';

        const sendMessage = async () => {
        const text = input.value.trim();
        if (!text) return;
        addMessage(text, 'user');
        input.value = '';

        const loader = addMessage('En train de réfléchir...', 'bot');
        try {
            const response = await fetch('../../Controllers/gemini.php', {  // ← Ajoute "s"
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ text: text })
});

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            if (data.response && data.response.includes('Désolé, reformule') || data.response.includes('indisponible')) {
                loader.remove();
                addMessage(data.response, 'bot');
            } else {
                loader.remove();
                addMessage(data.response || "Je n'ai pas de réponse pour le moment.", 'bot');
            }
        } catch (e) {
            console.error(e);
            loader.remove();
            addMessage("Erreur de connexion. Vérifie que gemini.php fonctionne.", 'bot');
        }
    };

    sendBtn.onclick = sendMessage;
    input.addEventListener('keypress', e => e.key === 'Enter' && sendMessage());

    function addMessage(fullText, type) {
    const div = document.createElement('div');
    div.className = `message ${type}`;

    if (type === 'bot') {
        let usefulText = fullText;

        // Liste des phrases à supprimer (ajoute-en d'autres si tu veux)
        const phrasesToRemove = [
            /Chez MedSense[, ]*nous (encourageons|recommandons|promouvons).*/i,
            /N['’]hésitez pas.*/i,
            /Prenez soin de vous.*/i,
            /Merci.*question.*/i,
            /Bonjour[ !]*.*/i,
            /Salut[ !]*.*/i,
            /Excellente question.*/i,
            /C'est une excellente question.*/i,
            /Je suis là pour vous aider.*/i,
            /À très bientôt.*/i,
            /Belle journée à vous.*/i,
            /Hydratez-vous bien.*/i,
            /.*MedSense team.*/i
        ];

        // On supprime toutes ces phrases
        phrasesToRemove.forEach(regex => {
            usefulText = usefulText.replace(regex, '');
        });

        // Nettoyage final : lignes vides, espaces, etc.
        usefulText = usefulText
            .replace(/^\s*[\r\n]+/gm, '')  // supprime lignes vides au début/milieu
            .replace(/\n{3,}/g, '\n\n')     // max 2 sauts de ligne
            .trim();

        // Si jamais tout a été supprimé (rare), on garde le texte original
        if (usefulText === '') usefulText = fullText.trim();

        // Affichage complet dans le chat (pour que ce soit joli
        div.innerHTML = fullText.replace(/\n/g, '<br>');

        // Mais c’est usefulText qui sera inséré au clic
        div.style.cursor = 'pointer';
        div.title = 'Clique pour copier ce conseil dans ta publication';

        div.onclick = function () {
            const input = document.getElementById('postInput');
            const current = input.value.trim();

            // Animation fluide
            this.style.transform = 'scale(0.95)';
            setTimeout(() => this.style.transform = '', 150);

            if (current === '') {
                input.value = usefulText;
            } else {
                input.value = current + '\n\n' + usefulText;
            }

            // Ferme le chat + focus
            document.getElementById('geminiChatWindow').style.display = 'none';
            input.focus();
            input.dispatchEvent(new Event('input')); // au cas où tu aurais un compteur de caractères
        };

        // Effet au survol
        div.addEventListener('mouseenter', () => {
            div.style.background = '#faf5ff';
            div.style.transform = 'translateY(-3px)';
            div.style.boxShadow = '0 10px 30px rgba(139,92,246,0.25)';
        });
        div.addEventListener('mouseleave', () => {
            div.style.background = '';
            div.style.transform = '';
            div.style.boxShadow = '';
        });
    } else {
        div.innerHTML = fullText.replace(/\n/g, '<br>');
    }

    document.querySelector('.chat-body').appendChild(div);
    document.querySelector('.chat-body').scrollTop = document.querySelector('.chat-body').scrollHeight;
    return div;
}
}

// OUVERTURE DU CHAT QUAND ON CLIQUE SUR L'ICÔNE ROBOT DU POST
document.getElementById('iconRobot')?.addEventListener('click', (e) => {
    e.stopPropagation(); // évite que le click se propage ailleurs
    document.getElementById('geminiChatWindow').style.display = 'flex';
});
</script>
<!-- ====================== FIN CHATBOT ====================== -->

</body>
</html>
