import { updateUserBalance } from "../api/api-update-balance.js";
import { showPopup } from "./showPopup.js";

export function manageAdv(type_adv) {
    return new Promise((resolve, reject) => {
        const AdController = window.Adsgram.init({ blockId: "8460" });
        AdController.show()
            .then((result) => {
                if (type_adv !== "no-reward") {
                    rewardUser();
                }
                resolve(result); // Risolve la Promise quando l'adv termina
            })
            .catch((error) => {
                showPopup("Error :( " + error.done);
                reject(error); // Rifiuta la Promise se c'è un errore
            });

        function rewardUser() {
            const userid = parseInt($("#user-id").text());
            const amount = 50; // L'importo da aggiungere
            const currentBalance = parseInt($("#CNSL-point").text()); // L'attuale saldo
            updateUserBalance(userid, amount, "claim", currentBalance);
        }
    });
}
