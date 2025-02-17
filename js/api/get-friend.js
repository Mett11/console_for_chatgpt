// get-friend.js
import { getCommonHeaders } from '../utils/manage-header.js'; // Assicurati di avere una funzione per gli header comuni

export async function getFriends(userId) {
    const response = await fetch('back-end/referral-code/get-friend.php', {
        method: 'POST',
        headers: getCommonHeaders(),
        body: JSON.stringify({
            user_id: userId
        })
    });

    if (!response.ok) {
        throw new Error('Errore nel recupero degli amici');
    }

    const data = await response.json();
    return data.friends; // Supponiamo che l'API ritorni un array di amici sotto la chiave "friends"
}
