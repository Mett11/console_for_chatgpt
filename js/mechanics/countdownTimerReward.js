import { formatTime } from "../utils/format.js";

export function startCountdownTimerReward(seconds, elementId) {
    const countdownElement = document.getElementById(elementId);

    const interval = setInterval(() => {
        if (seconds <= 0) {
            clearInterval(interval);
            countdownElement.innerText = "Reward available!";
        } else {
            countdownElement.innerText = `You have already claimed, come back later: ${formatTime(seconds)}`;
            seconds--;
        }
    }, 1000);
}
