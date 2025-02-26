//UPDATE HOMEPAGE DAILY REWARD
export function updateStreakDays(days) {
    if(days===undefined){
        days=0
    }
    document.getElementById("streak-days-reward").innerText = `Streak days: ${days}`;
}


export function showDailyRewardBox() {
    const rewardBox = document.querySelector(".daily-reward-box");
    rewardBox.style.display = "flex";
    rewardBox.style.flexDirection = "column";
}

export function hideDailyRewardBox() {
    document.querySelector(".daily-reward-box").style.display = "none";
}

