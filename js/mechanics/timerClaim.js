export function updateTimer(endTime) {

    let timerInterval = setInterval(function() {
        let now = new Date().getTime();
        let distance = endTime - now;

        if (distance <= 0) {
            clearInterval(timerInterval);
            document.getElementById("countdown").innerHTML = "00:00:00";
            $("#user-keys").prop("disabled", false);
            $("#user-keys").removeClass("user_keys_disabled").addClass("user_keys_enabled");
        } else {
            let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Funzione per formattare con uno zero davanti se il valore è inferiore a 10
            function padZero(num) {
                return num < 10 ? "0" + num : num;
            }

            // Formatta il countdown come HH:MM:SS
            document.getElementById("countdown").innerHTML = padZero(hours) + ":" + padZero(minutes) + ":" + padZero(seconds);

            $("#user-keys").prop("disabled", true);
            $("#user-keys").removeClass("user_keys_enabled").addClass("user_keys_disabled");
        }
    }, 1000);
}
