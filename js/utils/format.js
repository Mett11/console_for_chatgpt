// Formatta il tempo in "HH:MM:SS"
export function formatTime(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = seconds % 60;

    return `${padZero(hours)}:${padZero(minutes)}:${padZero(remainingSeconds)}`;
}

// Aggiunge uno zero davanti ai numeri singoli
export function padZero(number) {
    return number < 10 ? `0${number}` : `${number}`;
}
