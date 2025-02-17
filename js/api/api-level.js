import { getCommonHeaders } from '../utils/manage-header.js'; // Importa la funzione per gli header


export const getCurrentLevel = (user_id) => {
    return fetch(`back-end/get-current-level.php?user_id=${user_id}`, {
        method: 'GET',
        headers: getCommonHeaders(),
       
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Errore nel recuperare il livello attuale');
        }
        return response.json();
    });
};

export const saveLevel = async (user_id, livelloAttuale, nextLevel) => {
    const response = await fetch('back-end/save-level.php', {
        method: 'POST',
        headers: getCommonHeaders(),
        body: JSON.stringify({
            user_id: user_id,
            level: livelloAttuale,
            nextLevel: nextLevel
        })
    });
    if (!response.ok) {
        throw new Error('Errore nell\'aggiornare il livello');
    }
    return await response.json();
};
