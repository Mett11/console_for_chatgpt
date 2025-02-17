// Funzione per ottenere gli header comuni
export function getCommonHeaders() {
    const token = localStorage.getItem('jwt_token'); // Ottieni il token dal localStorage
    const headers = {
        'Content-Type': 'application/json',
    };

    // Se il token è presente, aggiungi l'Authorization header
    if (token) {
        headers['Authorization'] = 'Bearer ' + token;
    }

    return headers;
}
