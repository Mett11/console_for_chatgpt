// print-referral.js
import { getReferralCode } from '../api/get-ref-code.js';


export function onDomReadyRefCode() {
    document.addEventListener('DOMContentLoaded', async (event) => {
        try {
            const user_id = parseInt($("#user-id").text())
            const referralCode = await getReferralCode(user_id);
            const referralLink = `https://t.me/ConsoleGameApp_Bot?startapp=${referralCode}`;
            const referralContainer = document.getElementById("referral-code-container");
            referralContainer.innerHTML = ""; // Pulizia del contenitore

            for (let i = 0; i < referralCode.length; i++) {
                const box = document.createElement("div");
                box.className = "referral-box";
                box.innerText = referralCode.charAt(i);
                referralContainer.appendChild(box);
            }


            
            const copyButton = document.getElementById("copy-link-button");
            copyButton.addEventListener("click", function() {
                navigator.clipboard.writeText(referralLink).then(() => {
                });
            });

        } catch (error) {
            console.error('Errore nel recupero del codice referral:', error.message);
        }
    });
}




 