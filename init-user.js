import { showPopup } from "./js/utils/showPopup.js";
function generateReferralCode(userId) {
    const data = `${userId}-${Date.now()}`;
    const encoded = btoa(encodeURIComponent(data)); // Codifica in Base64
    return encoded.substring(0, 8); // Prendi i primi 8 caratteri
}

function initApp() {
    console.log("initApp start");

    console.log("navigator.userAgent:", navigator.userAgent);
    
    if (typeof Telegram !== 'undefined' && Telegram.WebApp && Telegram.WebApp.platform) {
        console.log("Telegram.WebApp.platform:", Telegram.WebApp.platform);
        if (!['android', 'ios'].includes(Telegram.WebApp.platform.toLowerCase())) {
            showPopup("Not available for Telegram Web, open from mobile");
            // qui potresti usare showPopup se definito
            return;
        }
    } else {
        console.log("Telegram.WebApp non definito, uso fallback userAgent");
        const isMobile = /Mobi|Android/i.test(navigator.userAgent);
        if (!isMobile) {

            return;
        }
    }

    // Continuazione del flusso solo se siamo su mobile
    const user_obj = Telegram.WebApp.initDataUnsafe.user;
    const initData = Telegram.WebApp.initData;
    const urlParams = new URLSearchParams(window.location.search);
    const referralCode = urlParams.get('tgWebAppStartParam');

    if (user_obj !== undefined) {
        // Prima richiesta: Registrazione utente
        fetch('back-end/register-user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                user_id: String(user_obj.id),
                init_data: initData
            })
        })
        .then(response => response.json())
        .then(data => {
        
            const token = data.token;
            localStorage.setItem('jwt_token', token);

            // Seconda richiesta: Aggiornamento sessione
            return fetch('back-end/update-session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}` // Aggiungi il token come Bearer Token
                },
                body: JSON.stringify({
                    key: String(user_obj.id)
                })
            });
        })
        .then(response => response.json())
        .then(sessionResponse => {
            console.log("Sessione aggiornata con successo:", sessionResponse);

            // Generazione del codice referral e salvataggio
            const userReferralCode = generateReferralCode(user_obj.id);
            return fetch('back-end/referral-code/save-referral-code.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('jwt_token')}` // Aggiungi il token come Bearer Token
                },
                body: JSON.stringify({
                    user_id: String(user_obj.id),
                    referral_code: userReferralCode,
                    referred_by: referralCode // Codice referral dall'URL (se presente)
                })
            });
        })
        .then(response => response.json())
        .catch(error => {
            if (error.message && error.message.includes('registrazione dell\'utente')) {
                window.location.href = "index.php";
            }
            console.error(error.message);
        });
    } else {
        document.getElementById("first-button-continue").disabled = true;
    }
}

// Se il documento è in fase di caricamento, attendi DOMContentLoaded; altrimenti, esegui subito initApp.
if (document.readyState === "loading") {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}
