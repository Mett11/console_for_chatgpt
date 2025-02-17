import { startCountdownTimer } from "../mechanics/countdownTimer.js"
import { getCommonHeaders } from '../utils/manage-header.js'; // Importa la funzione per gli header

export async function checkExistingCountdown(user_id) {
    try {
        const response = await fetch('back-end/get-user-info.php', {
            method: 'POST',
            headers: getCommonHeaders(),
            body: JSON.stringify({ user_id: user_id })
        });

        const result = await response.json();
        console.log("Risultato della risposta del server:", result);

        // Verifica se la data di fine del countdown è nel futuro
        if (result.countdown_end_time) {
            const countdownEndTime = new Date(result.countdown_end_time);
            const currentTime = new Date();
            
            // Se il countdown_end_time è nel futuro, il countdown è in corso
            return countdownEndTime > currentTime;
        }
        
        return false;  // Se non c'è countdown_end_time, considera che il countdown non è attivo
    } catch (error) {
        console.error("Errore durante la verifica del countdown:", error);
        return false; // Se c'è un errore, ritorna false
    }
}


export async function startCountdown(user_id) {
    try {
        const response = await fetch("back-end/countdown/start-countdown.php", {
            method: "POST",
            headers: getCommonHeaders(),
            body: JSON.stringify({ user_id: user_id })
        });

        const result = await response.json();
        console.log("Risultato dalla richiesta:", result);

        if (result.success) {
            const endTime = new Date(result.end_time).getTime();
            startCountdownTimer(endTime);
        } else {
            console.error("Errore durante la richiesta:", result.message);
        }
    } catch (error) {
        console.error("Errore durante la richiesta:", error);
    }
}
