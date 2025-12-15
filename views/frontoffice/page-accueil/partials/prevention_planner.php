<!-- Prevention Planner Widget -->
<section id="prevention-planner" style="max-width: 1100px; margin: 50px auto; padding: 0 15px;">

    <div
        style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 20px; color: white; padding: 40px; box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.3); position: relative; overflow: hidden;">

        <!-- Background Pattern -->
        <div
            style="position: absolute; top: 0; right: 0; width: 300px; height: 300px; background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%); border-radius: 50%; pointer-events: none;">
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 40px; position: relative; z-index: 1;">

            <!-- Left Side: Form -->
            <div style="flex: 1; min-width: 300px;">
                <h2 style="font-size: 2rem; margin-bottom: 10px; color: white;">Votre Planning Santé</h2>
                <p style="color: #94a3b8; margin-bottom: 30px; font-size: 1.1rem;">
                    Obtenez instantanément une feuille de route préventive personnalisée par IA selon votre profil.
                </p>

                <form id="preventionForm" style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <div style="flex: 1;">
                        <input type="number" id="prevAge" placeholder="Âge"
                            style="width: 100%; padding: 15px; border-radius: 12px; border: 2px solid #334155; background: #1e293b; color: white; font-size: 1rem; outline: none;">
                    </div>
                    <div style="flex: 1;">
                        <select id="prevGender"
                            style="width: 100%; padding: 15px; border-radius: 12px; border: 2px solid #334155; background: #1e293b; color: white; font-size: 1rem; outline: none;">
                            <option value="" disabled selected>Sexe</option>
                            <option value="Homme">Homme</option>
                            <option value="Femme">Femme</option>
                        </select>
                    </div>
                    <button type="submit"
                        style="background: #4f46e5; color: white; border: none; padding: 15px 30px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s; white-space: nowrap;">
                        Générer <i class="fas fa-magic" style="margin-left: 5px;"></i>
                    </button>
                    <!-- Custom Error Container -->
                    <div id="prevError"
                        style="width: 100%; color: #ef4444; font-size: 0.9rem; display: none; margin-top: 5px;"></div>
                </form>

                <div id="prevLoading" style="display: none; margin-top: 20px; color: #818cf8; font-style: italic;">
                    <i class="fas fa-spinner fa-spin"></i> L'IA analyse les recommandations médicales...
                </div>
            </div>

            <!-- Right Side: Results Timeline -->
            <div id="prevResults" style="flex: 1; min-width: 300px; display: none;">
                <h3
                    style="margin-top: 0; color: #818cf8; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">
                    Recommandations Prioritaires
                </h3>
                <div id="prevTimeline" style="margin-top: 20px; border-left: 2px solid #334155; padding-left: 20px;">
                    <!-- JS will inject contents here -->
                </div>
            </div>

        </div>
    </div>
</section>

<script>
    document.getElementById('preventionForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const btn = this.querySelector('button');
        const load = document.getElementById('prevLoading');
        const res = document.getElementById('prevResults');
        const timeline = document.getElementById('prevTimeline');
        const errDiv = document.getElementById('prevError');

        // 1. Reset UI
        res.style.display = 'none';
        timeline.innerHTML = '';
        errDiv.style.display = 'none';
        errDiv.innerText = '';

        // 2. Custom JS Validation
        const age = document.getElementById('prevAge').value.trim();
        const gender = document.getElementById('prevGender').value;

        if (!age || !gender) {
            errDiv.innerText = "⚠️ Veuillez remplir votre âge et votre sexe pour continuer.";
            errDiv.style.display = 'block';
            return; // Stop execution
        }

        if (age < 1 || age > 100) {
            errDiv.innerText = "⚠️ L'âge doit être compris entre 1 et 100 ans.";
            errDiv.style.display = 'block';
            return; // Stop execution
        }

        // 3. Proceed if valid
        btn.disabled = true;
        btn.style.opacity = '0.7';
        load.style.display = 'block';

        fetch('/projet_unifie/views/frontoffice/page-accueil/get_prevention_plan_endpoint.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ age: age, gender: gender })
        })
            .then(response => response.json())
            .then(data => {
                load.style.display = 'none';
                btn.disabled = false;
                btn.style.opacity = '1';

                if (Array.isArray(data)) {
                    res.style.display = 'block';
                    let delay = 0;
                    data.forEach(item => {
                        const itemDiv = document.createElement('div');
                        itemDiv.style.cssText = `margin-bottom: 25px; position: relative; opacity: 0; animation: fadeSlideIn 0.5s ease forwards ${delay}ms;`;
                        itemDiv.innerHTML = `
                    <div style="position: absolute; left: -26px; top: 0; width: 12px; height: 12px; background: #818cf8; border-radius: 50%;"></div>
                    <span style="display: block; color: #818cf8; font-weight: 700; font-size: 0.9rem; margin-bottom: 5px;">${item.month}</span>
                    <h4 style="margin: 0 0 5px 0; font-size: 1.2rem; color: white;">${item.checkup}</h4>
                    <p style="margin: 0 0 10px 0; color: #94a3b8; font-size: 0.95rem;">${item.reason}</p>
                    <a href="../rendezvous_avec_docteur/doctors_list.php" style="color: #38bdf8; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                        Prendre rendez-vous <i class="fas fa-arrow-right" style="font-size: 0.7em;"></i>
                    </a>
                `;
                        timeline.appendChild(itemDiv);
                        delay += 200;
                    });
                }
            })
            .catch(err => {
                console.error(err);
                load.innerText = "Erreur de connexion.";
            });
    });
</script>

<style>
    @keyframes fadeSlideIn {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
</style>