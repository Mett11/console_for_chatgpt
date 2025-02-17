import { 
    getTasks, 
    checkTaskCompletion, 
    claimTaskApi, 
    getUserBalance, 
    getDailyClaims 
} from "../api/api-task.js";
import { showPopup } from "../utils/showPopup.js";
import { updateUserBalance } from "../api/api-update-balance.js";
import { manageAdv } from "../utils/manageAdv.js";

const PARTNER_DAILY_LIMIT = 50; // Limite giornaliero per i task partner

export const loadTasks = (type, container, typeColors) => {
    getTasks(type)
        .then(tasks => {
            container.innerHTML = "";
            if (tasks.length === 0) {
                container.innerHTML = `<p class="no-task">No tasks available</p>`;
                return;
            }

            // Crea l'HTML per ogni task
            tasks.forEach(task => {
                const taskElement = document.createElement("div");
                taskElement.classList.add("task-item");
                const color = typeColors[type] || "#000";
                taskElement.innerHTML = `
                    <h3 style="color: ${color};">${task.nome_task}</h3>
                    <p>${task.claim_point} $CNSL</p>
                    ${
                        task.link_esterno
                        ? `<a href="${task.link_esterno}" target="_blank" class="claim-task-button" style="background-color: ${color};"
                              data-task-type="${task.type}" data-task-id="${task.id_task}" data-claim-point="${task.claim_point}">
                              Start</a>`
                        : `<button class="claim-task-button" style="background-color: ${color};"
                              data-task-type="${task.type}" data-task-id="${task.id_task}" data-claim-point="${task.claim_point}">
                              ${ task.type === "partner" ? `Watch 0/${PARTNER_DAILY_LIMIT}` : "Claim" }
                              </button>`
                    }
                `;
                container.appendChild(taskElement);
            });

            const userId = parseInt($('#user-id').text(), 10);

            // Gestione dei task già completati per gli altri tipi
            checkTaskCompletion(userId)
                .then(data => {
                    if (data.success) {
                        const completedTasks = data.completed_tasks.map(taskId => String(taskId));
                        const claimButtons = document.querySelectorAll(".claim-task-button");

                        claimButtons.forEach(button => {
                            const taskId = button.dataset.taskId;
                            const taskType = button.dataset.taskType;

                            if (taskType === "partner") {
                                // Aggiorna subito il testo del bottone con il valore corrente nel DB
                                getDailyClaims(userId, taskId, taskType)
                                    .then(data => {
                                        if (data.success) {
                                            const claimCount = parseInt(data.claimCount, 10);
                                            if (claimCount >= PARTNER_DAILY_LIMIT) {
                                                button.textContent = "No adv for today";
                                                button.disabled = true;
                                                button.style.backgroundColor = "grey";
                                            } else {
                                                button.textContent = `Watch ${claimCount}/${PARTNER_DAILY_LIMIT}`;
                                            }
                                        } else {
                                            button.textContent = "Error loading adv count";
                                        }
                                    })
                                    .catch(() => {
                                        button.textContent = "Error loading adv count";
                                    });

                                // Aggiungi l'evento click per i task partner
                                button.addEventListener("click", (e) => {
                                    if (button.disabled) return;
                                    button.disabled = true;
                                    button.textContent = "Loading...";

                                    // Prima verifica il conteggio corrente
                                    getDailyClaims(userId, taskId, taskType)
                                        .then(data => {
                                            if (data.success) {
                                                const claimCount = parseInt(data.claimCount, 10);
                                                if (claimCount >= PARTNER_DAILY_LIMIT) {
                                                    button.textContent = "No adv for today";
                                                    button.style.backgroundColor = "grey";
                                                    return;
                                                } else {
                                                    // Avvia l'ADV e, quando termina, esegue il claim
                                                    manageAdv("no-reward")
                                                        .then(() => {
                                                            return claimTask(
                                                                taskId, 
                                                                parseInt(button.dataset.claimPoint, 10), 
                                                                button, 
                                                                taskType
                                                            );
                                                        })
                                                        .then(() => {
                                                            // Dopo il claim, richiama getDailyClaims per ottenere il valore aggiornato
                                                            return getDailyClaims(userId, taskId, taskType);
                                                        })
                                                        .then((data) => {
                                                            if (data.success) {
                                                                const updatedCount = parseInt(data.claimCount, 10);
                                                                if (updatedCount >= PARTNER_DAILY_LIMIT) {
                                                                    button.textContent = "No adv for today";
                                                                    button.disabled = true;
                                                                    button.style.backgroundColor = "grey";
                                                                } else {
                                                                    button.textContent = `Watch ${updatedCount}/${PARTNER_DAILY_LIMIT}`;
                                                                    button.disabled = false;
                                                                }
                                                            } else {
                                                                showPopup(data.message);
                                                                button.disabled = false;
                                                            }
                                                        })
                                                        .catch((error) => {
                                                            button.disabled = false;
                                                            showPopup("Errore: " + error.message);
                                                        });
                                                }
                                            } else {
                                                showPopup("Errore nel recupero del conteggio ADV");
                                                button.disabled = false;
                                            }
                                        })
                                        .catch(error => {
                                            showPopup("Errore: " + error.message);
                                            button.disabled = false;
                                        });
                                });
                            } else {
                                // Per gli altri tipi di task
                                if (completedTasks.includes(taskId)) {
                                    const parentButton = button.parentElement;
                                    parentButton.classList.add('div-task-claimed');
                                    button.classList.add("claimed-task");
                                    button.textContent = "Claimed";
                                    button.disabled = true;
                                } else {
                                    button.addEventListener("click", (e) => {
                                        const taskId = e.target.dataset.taskId;
                                        const claimPoint = parseInt(e.target.dataset.claimPoint, 10);
                                        const taskType = e.target.dataset.taskType;
                                        disableBottomMenu();
                                        // Qui puoi chiamare claimTask senza la logica ADV se necessario
                                        claimTask(taskId, claimPoint, e.target, taskType)
                                            .catch(error => {
                                                showPopup("Errore: " + error.message);
                                            });
                                    });
                                }
                            }
                        });
                    } else {
                        container.innerHTML = `<p>${data.message}</p>`;
                    }
                })
                .catch(error => {
                    container.innerHTML = `<p>Error: ${error.message}</p>`;
                });
        })
        .catch(error => {
            container.innerHTML = `<p>Error: ${error.message}</p>`;
        });
};

//
// claimTask: restituisce una Promise che si risolve quando il DB è aggiornato
//
const claimTask = (taskId, claimPoint, button, taskType) => {
    const userId = parseInt($('#user-id').text(), 10);
    return new Promise((resolve, reject) => {
        getUserBalance(userId)
            .then(response => {
                const currentBalance = response.balance;
                getDailyClaims(userId, taskId, taskType)
                    .then(data => {
                        if (data.success && (taskType !== 'partner' || parseInt(data.claimCount, 10) < PARTNER_DAILY_LIMIT)) {
                            // Chiama l'API per completare il task (aggiornamento DB)
                            claimTaskApi(userId, taskId)
                                .then(apiData => {
                                    if (apiData.success) {
                                        const newCount = parseInt(data.claimCount, 10) + 1;
                                        // Aggiorna l'interfaccia con un breve delay
                                        button.classList.add("loading-button");
                                        button.style.backgroundColor = "grey";
                                        button.style.color = "black";
                                        button.textContent = "Loading...";
                                        setTimeout(() => {
                                            button.classList.remove("loading-button");
                                            button.style.backgroundColor = "";
                                            button.style.color = "";
                                            if (taskType === 'partner') {
                                                if (newCount < PARTNER_DAILY_LIMIT) {
                                                    button.textContent = `Watch ${newCount}/${PARTNER_DAILY_LIMIT}`;
                                                    button.disabled = false;
                                                } else {
                                                    button.textContent = "No adv for today";
                                                    button.disabled = true;
                                                    button.style.backgroundColor = "grey";
                                                }
                                            } else {
                                                button.textContent = "Task Claimed";
                                                button.disabled = true;
                                                button.style.backgroundColor = "grey";
                                            }
                                            updateUserBalance(userId, claimPoint, 'claim', currentBalance)
                                                .then(() => {
                                                    if (taskType === 'partner' && newCount >= PARTNER_DAILY_LIMIT) {
                                                        const parentButton = button.parentElement;
                                                        parentButton.classList.add('div-task-claimed');
                                                    }
                                                    enableBottomMenu();
                                                    resolve();
                                                })
                                                .catch(err => {
                                                    console.log("Errore durante l'aggiornamento del saldo:", err);
                                                    reject(err);
                                                });
                                        }, 7500);
                                    } else {
                                        showPopup(apiData.message);
                                        enableBottomMenu();
                                        reject(new Error(apiData.message));
                                    }
                                })
                                .catch(error => {
                                    alert("Errore durante il completamento del task: " + error.message);
                                    enableBottomMenu();
                                    reject(error);
                                });
                        } else {
                            showPopup(data.message || 'Hai raggiunto il limite giornaliero di task partner.');
                            enableBottomMenu();
                            reject(new Error(data.message || 'Limite raggiunto'));
                        }
                    })
                    .catch(error => {
                        alert("Errore durante la verifica dei claim giornalieri: " + error.message);
                        enableBottomMenu();
                        reject(error);
                    });
            })
            .catch(error => {
                alert("Errore durante il recupero del saldo: " + error.message);
                enableBottomMenu();
                reject(error);
            });
    });
};

//
// Funzioni per disabilitare e abilitare la bottom menu
//
const disableBottomMenu = () => {
    document.querySelector('.bottom-menu').style.pointerEvents = 'none';
};

const enableBottomMenu = () => {
    document.querySelector('.bottom-menu').style.pointerEvents = 'auto';
};
