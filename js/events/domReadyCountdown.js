import { keys } from "../utils/update-level.js";
import { updateUserBalance } from "../api/api-update-balance.js";
import { checkExistingCountdown, startCountdown } from "../api/api-countdown.js";
import { aggiornaLivello } from "../components/uiUpdateLevel.js";
import { manageAdv } from "../utils/manageAdv.js";

export function onDomReadyCountdown() {
    document.getElementById("user-keys").addEventListener("click", function() {
        let userId = parseInt($("#user-id").text());
        
        const keyId = $("#user-keys").attr("data-id");
        const item = keys.find(key => key.id === parseInt(keyId, 10));
        const claimValue = item.claim;

        let currentBalance = parseInt($('#CNSL-point').text().replace(/\./g, ''), 10);

        // Disabilita il bottone per impedire nuovi clic durante il countdown
        $("#user-keys").prop("disabled", true).removeClass("user_keys_enabled").addClass("user_keys_disabled");
        $("#countdown").addClass('active-countdown');

        // Controlla se l'utente ha già un countdown attivo o inattivo
        checkExistingCountdown(userId).then(isActive => {
            if (isActive) {
                // Se il countdown è ancora in corso, non fare nulla
                console.log("Un countdown è già in corso. Attendere il termine.");
            } else {
                // Se il countdown non è attivo, mostra l'adv e poi esegui le operazioni
                manageAdv("reward") // Avvia l'adv e aspetta che termini
                    .then(() => {
                        // Dopo che l'adv è finita, aggiorna il saldo e il livello
                        updateUserBalance(userId, claimValue, "claim", currentBalance)
                            .then(() => {
                                aggiornaLivello(userId, currentBalance + claimValue)
                                    .then(() => {
                                        // Solo dopo aver aggiornato il livello, avvia il countdown
                                        startCountdown(userId);
                                    })
                                    .catch(error => {
                                        console.error('Errore nell\'aggiornamento livello:', error);
                                    });
                            })
                            .catch(error => {
                                console.error('Errore nell\'aggiornamento saldo:', error);
                            });
                    })
                    .catch(error => {
                        console.error('Errore durante la gestione dell\'adv:', error);
                    });
            }
        });
    });
}
