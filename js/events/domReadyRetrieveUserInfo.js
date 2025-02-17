import { retrieve_user_info } from "../api/retrieve-user-info.js";
import { startCountdownTimer } from "../mechanics/countdownTimer.js";
import { updateUserUI } from "../components/uiUpdatesRetrieveUserInfo.js";

export function onDomReadyRetrieveUserInfo() {
    $(document).ready(async function () {
        try {
            const userId = parseInt($('#user-id').text());
            console.log("Fetching data for user ID:", userId);

            const data = await retrieve_user_info(userId);

            // Aggiorna la UI
            updateUserUI(data);

            // Avvia il timer se necessario
            if (data.countdown_end_time) {
                let countdownEndTime = new Date(data.countdown_end_time);
                startCountdownTimer(countdownEndTime);
            }
        } catch (error) {
            console.error('Errore durante il caricamento:', error);
        }
    });
}
