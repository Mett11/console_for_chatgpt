// api-get-referral.js
import { getCommonHeaders } from '../utils/manage-header.js'; // Importa la funzione per gli header

export async function getReferralCode(userId) {

    const response = await fetch(`back-end/referral-code/get-referral-code.php?user_id=${userId}`, {
        method: 'GET',
        headers: getCommonHeaders()  // se necessario
    });
    
    if (!response.ok) {
        throw new Error('Errore nel recupero del codice referral');
    }

    const data = await response.json();
    return data.referral_code;
}
