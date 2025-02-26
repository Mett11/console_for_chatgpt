import { showPopup } from "./js/utils/showPopup.js";

function generateReferralCode(userId) {
    const data = `${userId}-${Date.now()}`;
    const encoded = btoa(encodeURIComponent(data)); // Codifica in Base64
    return encoded.substring(0, 8); // Prendi i primi 8 caratteri
}

function initApp() {
    console.log("initApp start");
    console.log("navigator.userAgent:", navigator.userAgent);

    // Seleziona gli elementi di loading e main content
    const loadingScreen = document.getElementById("loading-screen");
    const mainContent = document.querySelector(".container_homepage");

    if (typeof Telegram !== 'undefined' && Telegram.WebApp && Telegram.WebApp.platform) {
        console.log("Telegram.WebApp.platform:", Telegram.WebApp.platform);
        if (!['android', 'ios'].includes(Telegram.WebApp.platform.toLowerCase())) {
            showPopup("Not available for Telegram Web, open from mobile");
            return;
        }
    } else {
        console.log("Telegram.WebApp non definito, uso fallback userAgent");
        const isMobile = /Mobi|Android/i.test(navigator.userAgent);
        if (!isMobile) return;
    }

    // Continuazione del flusso solo se siamo su mobile
    const user_obj = Telegram.WebApp.initDataUnsafe.user;
    const initData = Telegram.WebApp.initData;
    const urlParams = new URLSearchParams(window.location.search);
    const referralCode = urlParams.get('tgWebAppStartParam');

    if (user_obj !== undefined) {
        const user_id = String(user_obj.id);
        // Salva lo user_id in localStorage
        localStorage.setItem('user_id', user_id);
        // Imposta il link per homepage.php inizialmente a "#" per evitare reindirizzamenti prematuri
        document.getElementById("link_homepage").href = "#";
        // Disabilita il bottone finché non arriva la risposta positiva
        document.getElementById("first-button-continue").disabled = true;

        // Prima richiesta: Registrazione utente
        fetch('back-end/register-user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: user_id,
                init_data: initData
            })
        })
        .then(response => response.json())
        .then(data => {
            const token = data.token;
            // Salva il token in localStorage e in un cookie
            localStorage.setItem('jwt_token', token);
            document.cookie = `jwt_token=${encodeURIComponent(token)}; path=/; Secure; SameSite=Strict`;

            // Genera il codice referral e salvalo
            const userReferralCode = generateReferralCode(user_id);
            return fetch('back-end/referral-code/save-referral-code.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}` // Il token qui viene passato per autorizzare la richiesta
                },
                body: JSON.stringify({
                    user_id: user_id,
                    referral_code: userReferralCode,
                    referred_by: referralCode // (se presente)
                })
            });
        })
        .then(response => {
            console.log("Status code:", response.status);
            if (!response.ok) {
                throw new Error('Errore nel salvataggio del codice referral');
            }
            return response.text();
        })
        .then(text => {
            console.log("Response text:", text);
            let data;
            try {
                data = JSON.parse(text);
            } catch(e) {
                throw new Error("Errore nel parsing della response JSON: " + e.message);
            }
            return data;
        })
        .then(data => {
            if (data.success === true) {
                document.getElementById("link_homepage").href = `homepage.php?user_id=${user_id}`;
                document.getElementById("first-button-continue").disabled = false;
                loadingScreen.style.display = "none";
                mainContent.style.display = "block";
            } else {
                throw new Error(data.message || 'Errore sconosciuto nel salvataggio del referral code');
            }
        })
        .catch(error => {
            loadingScreen.style.display = "none";
            showPopup("Si è verificato un errore. Riprova.");
        });
        
        
    } else {
        document.getElementById("first-button-continue").disabled = true;
        loadingScreen.style.display = "none";
    }
}

// Esegui initApp() al caricamento del documento
if (document.readyState === "loading") {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}
