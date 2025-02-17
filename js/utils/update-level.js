export const keys = [
    { id: 1, img: 'img/button/alt-button.svg', cost: 0, claim: 50, time: 5 },
    { id: 2, img: 'img/button/ctrl-button.svg', cost: 200, claim: 200, time: 10 },
    { id: 3, img: 'img/button/del-button.svg', cost: 300, claim: 300, time: 15 },
    { id: 4, img: 'img/button/shift-button.svg', cost: 400, claim: 400, time: 20 },
    { id: 5, img: 'img/button/enter-button.svg', cost: 500, claim: 500, time: 25 }
];

export const calcolaLivello = (punti, livelloPrecedente) => {    
    if (punti < 10000) return Math.max(1, livelloPrecedente); // Livello minimo è 1
    if (punti < 25000) return Math.max(2, livelloPrecedente); // Livello minimo è 2
    if (punti < 50000) return Math.max(3, livelloPrecedente); // Livello minimo è 3
    if (punti < 75000) return Math.max(4, livelloPrecedente); // Livello minimo è 4
    if (punti < 100000) return Math.max(5, livelloPrecedente); // Livello minimo è 5
    if (punti < 200000) return Math.max(6, livelloPrecedente); // Livello minimo è 6
    if (punti < 500000) return Math.max(7, livelloPrecedente); // Livello minimo è 7
    if (punti < 1000000) return Math.max(8, livelloPrecedente); // Livello minimo è 8
    if (punti < 2000000) return Math.max(9, livelloPrecedente); // Livello minimo è 9
    return Math.max(10, livelloPrecedente); // Livello massimo è 10
};

export const calcolaNextLevel = (punti) => {
    if (punti < 10000) return 10000;
    if (punti < 25000) return 25000;
    if (punti < 50000) return 50000;
    if (punti < 75000) return 75000;
    if (punti < 100000) return 100000;
    if (punti < 200000) return 200000;
    if (punti < 500000) return 500000;
    if (punti < 1000000) return 1000000;
    if (punti < 2000000) return 2000000;
    return 2000000;
};

export const getColor = (livello) => {
    const userLevelElement = document.getElementById('user-level');
    const userCountdown = document.getElementById('countdown');
    const userNextLevelPoints = document.getElementById('next-level-point');
    const openKeysElement = document.getElementById('open-keys');

    if (userLevelElement) {

        // Rimuoviamo le classi precedenti
        userLevelElement.classList.remove('level-2-3', 'level-4-5', 'level-6-7', 'level-8-9', 'level-10');
        userCountdown.classList.remove('level-2-3', 'level-4-5', 'level-6-7', 'level-8-9', 'level-10');
        userNextLevelPoints.classList.remove('level-2-3', 'level-4-5', 'level-6-7', 'level-8-9', 'level-10');
        openKeysElement.classList.remove('level-openk-2-3', 'level-openk-4-5', 'level-openk-6-7', 'level-openk-8-9', 'level-openk-10');

        // Determinare la classe da aggiungere in base al livello
        let levelClass = "";
        let levelOpenKeys = "";

        if (livello == 1) {
            levelClass = "level-1";
        } else if (livello == 2 || livello == 3) {
            levelClass = "level-2-3";
            levelOpenKeys = "level-openk-2-3";
        } else if (livello == 4 || livello == 5) {
            levelClass = "level-4-5";
            levelOpenKeys = "level-openk-4-5";
        } else if (livello == 6 || livello == 7) {
            levelClass = "level-6-7";
            levelOpenKeys = "level-openk-6-7";
        } else if (livello == 8 || livello == 9) {
            levelClass = "level-8-9";
            levelOpenKeys = "level-openk-8-9";
        } else if (livello == 10) {
            levelClass = "level-10";
            levelOpenKeys = "level-openk-10";
        }

        // Aggiungere la classe al primo elemento
        if (userLevelElement && levelClass) {
            userLevelElement.classList.add(levelClass);
        }

        // Aggiungere la classe al secondo elemento
        if (userCountdown && levelClass) {
            userCountdown.classList.add(levelClass);
        }

        // Aggiungere la classe al terzo elemento
        if (userNextLevelPoints && levelClass) {
            userNextLevelPoints.classList.add(levelClass);
        }

        // Aggiungere la classe all'elemento open-keys se definito
        if (openKeysElement && levelOpenKeys) {
            openKeysElement.classList.add(levelOpenKeys);
        }
    } else {
        console.log("NON SIAMO SULLA HOMEPAGE");
    }
};
