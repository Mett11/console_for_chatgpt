import { loadTasks } from "../components/uiUpdatesTask.js";

export function onDomReadyTask() {
    document.addEventListener("DOMContentLoaded", () => {
        const buttons = document.querySelectorAll(".task-type-button");
        const container = document.querySelector(".container-task-by-type");

        // Definizione colori per tipo di task
        const typeColors = {
            game: "#E07A5F",
            social: "#FAFFFD",
            partner: "#C3CCFF",
        };

        // Associa gli event listener ai pulsanti
        buttons.forEach(button => {
            button.addEventListener("click", function () {
                const type = this.id.split("-")[0]; // Esempio: 'game-activity-button' -> 'game'
                loadTasks(type, container, typeColors);
            });
        });

        // Chiamata alla funzione di caricamento task
        loadTasks('game', container, typeColors);  // Carica i task di tipo 'game' all'inizio
    });
}