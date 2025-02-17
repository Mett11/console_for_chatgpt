import { getCommonHeaders } from '../utils/manage-header.js'; // Importa la funzione per gli header


export async function retrieve_user_info(userId){
    
    
    const response = await fetch('back-end/get-user-info.php', {
        method: 'POST',
        headers: getCommonHeaders(),
        body: JSON.stringify({ user_id: userId })  // Passa i dati nel corpo della richiesta
    });

    if (!response.ok) {
        throw new Error('Errore nella richiesta: ' + response.status);
    }
    
    return response.json();
}