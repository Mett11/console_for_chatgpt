import { getColor } from '../utils/update-level.js';

//UPDATE HOMEPAGE RETRIEVE USER INFO
export function updateUserUI(data) {
    $('#user-id').text(data.user_id);
    $('#user-level').text(data.user_level);
    $('#CNSL-point').text(data.balance);
    $('#next-level-point').text(data.next_level_points);
    $('#PxD').text("PPD " + data.ppd_value);
    $("#open-keys").css("display", "block")
    getColor(parseInt(data.user_level));

    if (data.countdown_end_time) {
        $("#user-keys").prop("disabled", true);
        $(".box-start-button").addClass("box-start-button-countdown");
    } else {
        $(".box-start-button").removeClass("box-start-button-countdown");
        $("#user-keys").prop("disabled", false);
        $('#countdown').text("00:00:00");
    }
}


