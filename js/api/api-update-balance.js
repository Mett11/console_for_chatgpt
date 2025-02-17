
import { handleUpdateUserBalance } from '../components/uiUpdateBalance.js'; // Importa la funzione che aggiorna la UI

import { getCommonHeaders } from '../utils/manage-header.js'; // Importa la funzione per gli header

// Funzione per aggiornare il saldo e passare la risposta al metodo di aggiornamento UI
export const updateUserBalance = async (userId, amount, type, currentBalance) => {
    try {
        const response = await fetch('back-end/update-balance.php', {
            method: 'POST',
            headers: getCommonHeaders(),
            body: JSON.stringify({
                user_id: userId,
                amount: amount,
                type: type
            })
        });

        if (!response.ok) {
            throw new Error("Errore nella risposta dal server");
        }

        const data = await response.json(); // Riceve i dati dalla risposta

        // Chiamata al metodo che gestisce l'aggiornamento della UI, passando la risposta
        handleUpdateUserBalance(userId, amount, type, currentBalance, data);

    } catch (error) {
        console.error("Errore API:", error);
        throw error;
    }
};

