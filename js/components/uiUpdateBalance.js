// main.js

import { showPopup } from "../utils/showPopup.js"; // Importa le funzioni per l'aggiornamento della UI
import { aggiornaLivello } from "../components/uiUpdateLevel.js"; // Importa le funzioni per l'aggiornamento della UI

// Funzione per gestire l'aggiornamento del saldo e della UI
export const handleUpdateUserBalance = (userId, amount, type, currentBalance, data) => {
    console.log("Fetch Success:", data);

    if (data.success) {
        console.log("Saldo aggiornato con successo.");

        // Aggiorna il saldo visualizzato sulla UI
        if (type === 'purchase') {
            currentBalance -= amount;
            $('#CNSL-point').text(currentBalance);
        } else if (type === 'claim') {
            currentBalance += amount;
            $('#CNSL-point').text(currentBalance);
            showPopup(amount + " $CNSL Claimed");
            aggiornaLivello(userId, currentBalance);
        }
    } else {
        console.log("Errore durante l'aggiornamento del saldo:", data.error);
        throw new Error(data.error);
    }
};
