import { padZero } from "../utils/format.js";

export function startCountdownTimer(endTime) {
    const userLevelElement = document.getElementById('user-level');
    if (userLevelElement) {
        let timerInterval = setInterval(function () {
            let now = new Date().getTime();
            let distance = endTime - now;

            if (distance <= 0) {
                clearInterval(timerInterval);
                document.getElementById("countdown").innerHTML = "00:00:00";

                $(".box-start-button").removeClass("box-start-button-countdown");
                $("#user-keys").prop("disabled", false);
                $("#user-keys").removeClass("user_keys_disabled").addClass("user_keys_enabled");
            } else {
                let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                let seconds = Math.floor((distance % (1000 * 60)) / 1000);

                document.getElementById("countdown").innerHTML =
                    padZero(hours) + ":" + padZero(minutes) + ":" + padZero(seconds);

                $(".box-start-button").addClass("box-start-button-countdown");
                $("#user-keys").prop("disabled", true).removeClass("user_keys_enabled").addClass("user_keys_disabled");
            }
        }, 1000);
    }
}
