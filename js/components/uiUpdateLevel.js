import { getCurrentLevel, saveLevel } from "../api/api-level.js";
import { calcolaLivello, calcolaNextLevel, getColor } from "../utils/update-level.js";


export async function aggiornaLivello(user_id, punti) {
    try {
        const response = await getCurrentLevel(user_id);
        const livelloCorrente = response.level;
        const nextPointCorrente = response.next_level_points;

        // Calcola il livello in base ai punti attuali, tenendo conto del livello precedente
        const livelloAttuale = calcolaLivello(punti, livelloCorrente);

        // Calcola il prossimo livello da raggiungere
        const nextLevel = calcolaNextLevel(punti);

        if (livelloAttuale !== livelloCorrente) {
            // Se il livello è cambiato (cioè l'utente ha raggiunto un nuovo livello), aggiorna il livello
            const updateResponse = await saveLevel(user_id, livelloAttuale, nextLevel);
            $("#user-level").text(livelloAttuale);
            $("#next-level-point").text(nextLevel);
            getColor(livelloAttuale);
            // Mostra il popup per il nuovo livello raggiunto
            //showLevelUpPopup(livelloCorrente);
        } else {
            // Se il livello non è cambiato, aggiorna comunque l'interfaccia
            $("#user-level").text(livelloCorrente);
            $("#next-level-point").text(nextPointCorrente);
            //showLevelUpPopup(livelloCorrente);
        }
    } catch (error) {
        console.error('Errore:', error.message);
    }
}
