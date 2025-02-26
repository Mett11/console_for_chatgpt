import { check_daily_rewards } from "../api/check-daily-reward.js";
import { updateUserBalance } from "../api/api-update-balance.js";
import { startCountdownTimerReward } from "../mechanics/countdownTimerReward.js";
import { updateStreakDays, showDailyRewardBox, hideDailyRewardBox } from "../components/uiUpdatesDailyReward.js";

export function onDomReadyDailyReward() {
    document.addEventListener("DOMContentLoaded", () => {
        document.getElementById("daily-reward-button").addEventListener("click", async () => {
                try {
                    const userid = parseInt($("#user-id").text())
                    const data = await check_daily_rewards(userid);

                    if (data.message === "Claim effettuato") {
                        const userId = parseInt(document.getElementById("user-id").innerText, 10);
                        const currentBalance = Number(document.getElementById("CNSL-point").innerText);

                        // Aggiorna il saldo dell'utente
                        // (ipotizziamo che la funzione updateUserBalance sia già gestita altrove)
                        updateUserBalance(userId, data.points, "claim", currentBalance);

                        updateStreakDays(data.consecutive_days);
                    } else {
                        const timeRemainingSeconds = parseTimeString(data.time_remaining);
                        startCountdownTimerReward(timeRemainingSeconds, "countdown-daily-reward");
                        updateStreakDays(data.consecutive_days);
                    }
                } catch (error) {
                    console.error("Errore durante il claim del daily reward:", error);
                }
            });

            document.getElementById("open-daily-button").addEventListener("click", showDailyRewardBox);
            document.getElementById("close-daily-reward-box").addEventListener("click", hideDailyRewardBox);
    });
}

// Converte "HH:MM:SS" in secondi
function parseTimeString(timeString) {
    const [hours, minutes, seconds] = timeString.split(":").map(Number);
    return (hours * 3600) + (minutes * 60) + seconds;
}
