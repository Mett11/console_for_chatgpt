import { getCommonHeaders } from '../utils/manage-header.js'; // Importa la funzione per gli header

export async function check_daily_rewards() {
    const response = await fetch('back-end/daily-reward/daily-reward.php', {
        method: 'POST',
        headers: getCommonHeaders(),
        body: JSON.stringify({ action: 'claim' }),
    });

    if (!response.ok) {
        throw new Error("Errore durante la richiesta daily-reward");
    }

    return await response.json();
}
