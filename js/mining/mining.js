import { showPopup } from "../utils/showPopup.js";
import { updateUserBalance } from "../api/api-update-balance.js";
import { getPurchaseItems, startFarmingAPI, savePurchase, claimFarmingPointsAPI, checkFarmingStatusAPI } from "../api/api-farming.js";
import { retrieve_user_info } from "../api/retrieve-user-info.js";
import { processUsdtPayment } from "./payments/process_usdt.js";

document.addEventListener("DOMContentLoaded", async () => {
    // Inizializza l'istanza di TonConnectUI
    const tonConnectUI = new TON_CONNECT_UI.TonConnectUI({
      manifestUrl: 'https://raw.githubusercontent.com/Mett11/tg_console_repo/refs/heads/main/tonconnect-manifest.json',
      buttonRootId: 'ton-connect'  // Puoi avere questo elemento nascosto se non vuoi mostrare il bottone
    });
  
    // Verifica se il wallet è già connesso
    if (tonConnectUI.wallet) {
        console.log("Wallet connesso:", tonConnectUI.wallet.account.address);
        window.tonWallet = tonConnectUI.wallet; // Imposta la variabile globale con l'istanza reale
    } else {
        console.log("Nessun wallet connesso. Richiedi la connessione.");
        // Puoi, ad esempio, mostrare un messaggio all'utente o addirittura forzare una richiesta di connessione
        // oppure reindirizzarlo a wallet.php.
        window.tonWallet = null;
    }
  });
  
  

document.getElementById("miningButton").addEventListener("click", function() {
    const balance = $("#CNSL-point").text()
    $("#CNSL-point-mining").text("$CNSL "+balance)
});


document.addEventListener("click", function (event) {
    const disabledElement = event.target.closest(".disabled-component");
    if (disabledElement) {
        showPopup("Wait for farming to finish or redeem the claim");
    }
});

async function fetchPurchasedItems_INIT() {
    let userId = parseInt($("#user-id").text());
    await getPurchaseItems(userId) // Endpoint che restituisce gli oggetti acquistati
        .then(data => {
            if (data.success) {
                // Splitta la stringa degli articoli acquistati
                let items = String(data.purchasedItems).split(',');

                // Carica il file JSON mining-components.json
                fetch('js/mining/mining-components.json')
                    .then(response => response.json())
                    .then(data => {
                        // Verifica che data abbia la chiave "components" e che sia un array
                        if (!data.components || !Array.isArray(data.components)) {
                            throw new TypeError('Il JSON caricato non contiene un array "components"');
                        }

                        let miningComponents = data.components;

                        items.forEach(item => {
                            // Trova il nome dell'articolo rimuovendo eventuali spazi
                            let itemName = item.trim();

                            // Trova l'elemento corrispondente nel JSON
                            let foundComponent;
                            miningComponents.some(component => {
                                return component.items.some(i => {
                                    if (i.name === itemName) {
                                        foundComponent = { 
                                            type: component.type, 
                                            name: i.name,
                                            level: i.level, 
                                            src: i.src,
                                            miningProfit: i.mining_profit_cns // Aggiunge il profitto del livello corrente
                                        };
                                        return true;
                                    }
                                });
                            });

                            if (foundComponent) {
                                // Calcolare il totale di `mining_profit_cns` per i livelli attivi
                                let totalMiningProfit = 0;

                                // Trova tutti i livelli del tipo corrente
                                let currentComponent = miningComponents.find(c => c.type === foundComponent.type);

                                if (currentComponent) {
                                    currentComponent.items.forEach(levelItem => {
                                        if (levelItem.level <= foundComponent.level) {
                                            totalMiningProfit += levelItem.mining_profit_cns;
                                        }
                                    });
                                }

                                // Aggiorna l'elemento HTML con il totale calcolato
                                let element = document.getElementById(foundComponent.type);
                                if (element) {
                                    element.classList.add('level-enabled');
                                    element.innerHTML = `
                                        <h1 class="type-component">${foundComponent.type}</h1>
                                        <h1 class='level-component'>Level ${foundComponent.level}</h1>
                                        <h1 class='name-component'>${foundComponent.name}</h1>`;
                                }
                            }
                        });
                    })
                    .catch(error => console.error('Errore nel caricamento del file mining-components.json:', error));

            } 
            else {
                console.log('Errore nel recupero degli oggetti acquistati');
            }
        })
        .catch(error => {
            console.error('Errore nel recupero degli oggetti acquistati:', error);
        });
}




//***************************INIZIO LOGICA FARMING*********************************** */

// Carica il JSON dal file esterno
fetch('js/mining/mining-components.json')
    .then(response => {
        if (!response.ok) {
            throw new Error('Errore durante il caricamento del file JSON.');
        }
        return response.json();
    })
    .then(jsonData => {
        initializeComponents(jsonData);
    })
    .catch(error => {
        console.error('Errore:', error);
    });

// Funzione per aggiornare il saldo e chiamare l'API
function updateBalance(userId, amount, type) {
    const CNSLPointElement = document.getElementById('CNSL-point-mining');
    let currentBalance = parseInt(CNSLPointElement.textContent.replace("$CNSL", "").trim(), 10) || 0;

    // Calcola il nuovo saldo
    const newBalance = currentBalance - amount;

    // Simula la chiamata all'API
    updateUserBalance(parseInt(userId), amount, type, currentBalance);

    // Aggiorna il saldo visualizzato
    
    CNSLPointElement.textContent = `$CNSL ${newBalance}`;
    
    return newBalance; // Restituisce il nuovo saldo
}

// Funzione per creare un popup con i dettagli e opzioni di acquisto
function createPopup(content, jsonData, userId, id_box) {
    const popup = document.createElement('div');
    popup.classList.add('popup-hw');
    popup.innerHTML = `
        <div class="popup-content">
            <div class="popup-header">
                <h2>Upgrade your <span class='type-hw-header'>${id_box}</span></h2>
                <button class="close-popup">&times;</button>
            </div>
            <div class="popup-body">
                ${content}
            </div>
        </div>
    `;
    document.body.appendChild(popup);

    // Funzione per inviare i dettagli dell'acquisto al server PHP
    async function savePurchaseUi(userId, itemData, typeHW) {
        await savePurchase(userId, itemData, typeHW)

        .then(data => {
            if (data.success) {
                console.log('Acquisto salvato con successo!');
            // Recupera l'elemento principale
                let element = document.getElementById(typeHW);

                // Svuota il contenuto esistente
                element.innerHTML = '';

                // Aggiungi la classe
                element.classList.add('level-enabled');



                // Aggiungi il testo (tipo di hardware e livello)
                let typeText = document.createElement('h1');
                typeText.classList.add('type-component');
                typeText.textContent = `${typeHW}`;
                element.appendChild(typeText);

                // Aggiungi l'elemento <h1> con il livello
                let levelHeading = document.createElement('h1');
                levelHeading.classList.add('level-component');
                levelHeading.textContent = `Level ${itemData.level}`;
                element.appendChild(levelHeading);

                // Aggiungi il testo (tipo di hardware e livello)
                let nameText = document.createElement('h1');
                nameText.classList.add('name-component');
                nameText.textContent = `${itemData.name}`;
                element.appendChild(nameText);

            } else {
                console.error('Errore dal server:', data.message);
            }
        })
        .catch(error => {
            console.error('Errore:', error);
        });
    }

// Aggiornamento della funzione buy-button
popup.querySelectorAll('.buy-button').forEach(button => {
    button.addEventListener('click', () => {
        
        const CNSLPointElement = document.getElementById('CNSL-point-mining');
        let currentBalance = parseInt(CNSLPointElement.textContent.replace("$CNSL", "").trim(), 10) || 0;
        const itemData = JSON.parse(button.dataset.item);
        console.log(itemData);

        const popupHeader = document.querySelector('.popup-header');
        const typeHwHeader = popupHeader.querySelector('.type-hw-header');
        const typeText = typeHwHeader.textContent;

        if (itemData.isPurchased) {
            showPopup("Item already purchased");
            return; // Se l'item è già stato acquistato, esci.
        }

        if (itemData.price_cns !== null) {
            // Acquisto con punti CNSL (già implementato)
            if (currentBalance >= itemData.price_cns) {
                const newBalance = updateBalance(userId, itemData.price_cns, 'purchase');
                // Aggiorno il PPD e registro l'acquisto
                const rawText = document.getElementById('PxD').textContent.replace('PPD ', '').trim();  
                const currentPPD = parseInt(rawText, 10) || 0; 
                document.getElementById('PxD').textContent = `PPD ${currentPPD + itemData.mining_profit_cns}`;
                savePurchaseUi(userId, itemData, typeText);
                itemData.isPurchased = true;
                button.disabled = true;
                button.textContent = 'Bought';
                // Abilita il livello successivo, se presente
                const nextLevelButton = Array.from(popup.querySelectorAll('.buy-button')).find(b => {
                    const nextItemData = JSON.parse(b.dataset.item);
                    return nextItemData.level === itemData.level + 1;
                });
                if (nextLevelButton) {
                    nextLevelButton.disabled = false;
                    nextLevelButton.textContent = 'Buy Now';
                }
                currentBalance = newBalance;
            } else {
                showPopup("Insufficient balance");
            }
        } else if (itemData.price_usdt !== null) {
            processUsdtPayment(itemData)
            .then(() => {
                console.log("Updating UI after successful payment...");
        
                // 🔹 Aggiorna il valore di PPD
                const rawText = document.getElementById('PxD').textContent.replace('PPD ', '').trim();  
                const currentPPD = parseInt(rawText, 10) || 0; 
                document.getElementById('PxD').textContent = `PPD ${currentPPD + itemData.mining_profit_cns}`;
        
                // 🔹 Registra l'acquisto nel database
                savePurchaseUi(userId, itemData, typeText);
        
                // 🔹 Disabilita il bottone dell'item acquistato
                itemData.isPurchased = true;
                button.disabled = true;
                button.textContent = 'Bought';
        
                // 🔹 Abilita il pulsante successivo
                const nextLevelButton = Array.from(document.querySelectorAll('.buy-button')).find(b => {
                    const nextItemData = JSON.parse(b.dataset.item);
                    return nextItemData.level === itemData.level + 1;
                });
        
                if (nextLevelButton) {
                    console.log("Enabling next level button:", nextLevelButton);
                    nextLevelButton.disabled = false;
                    nextLevelButton.textContent = 'Buy Now';
                }
            })
            .catch(error => {
                console.error("Payment Error:", error);
                showPopup("Payment failed: " + error);
            });
        

        }
    });
});

    // Chiusura popup
    popup.querySelector('.close-popup').addEventListener('click', () => {
        document.body.removeChild(popup);
    });
    
}

// Funzione per cercare i dettagli nel JSON con pulsanti di acquisto
function getDetailsByType(type, jsonData, userId) {
    const component = jsonData.components.find(c => c.type === type);
    if (!component || !component.items) return '<p>Nessun dato disponibile.</p>';
    return component.items.map(item => `
        <div class="item">
            <h3>${item.name} (Level ${item.level})</h3>
            <div class="container-img-hw-item"><img class="img-hw-item" src="${item.src}"></img></div>
            ${item.price_cns ? `<p >Price: <span class="currency-hw">$CNSL</span> ${item.price_cns}</p>` :''}
            ${item.price_usdt ? `<p >Price <span class="currency-hw-ton">TON</span> ${item.price_usdt}</p>` : ''}
            <p>PPD: ${item.mining_profit_cns}</p>

            <button class="buy-button" data-item='${JSON.stringify(item)}' 
            ${item.isPurchased || item.level > 1 && !component.items.find(i => i.level === item.level - 1)?.isPurchased ? 'disabled' : ''}>
                ${
                    item.isPurchased
                        ? 'Bought' // Se è acquistato
                        : (item.level > 1 && !component.items.find(i => i.level === item.level - 1)?.isPurchased)
                            ? 'Locked' // Se non è acquistabile per il livello
                            : 'Buy Now' // Se è acquistabile
                }
            </button>
        </div>
    `).join('');
    
}

// Inizializza il comportamento degli elementi dopo aver caricato il JSON
function initializeComponents(jsonData) {
    const userId = document.getElementById('user-id').textContent.trim();

    document.querySelectorAll('.component').forEach(element => {
        let id_box = element.id
        element.addEventListener('click', () => {

            const type = element.id;
            const details = getDetailsByType(type, jsonData, userId);
            createPopup(details, jsonData, userId, id_box);
        });
    });
}


async function fetchPurchasedItems() {
    let userId = parseInt($("#user-id").text());
    await getPurchaseItems(userId)
        .then(data => {
            if (data.success) {
                disableBoughtItems(data.purchasedItems); // Disabilita i bottoni degli oggetti acquistati

            } else {
                console.log('Errore nel recupero degli oggetti acquistati');
            }
        })
        .catch(error => {
            console.error('Errore nel recupero degli oggetti acquistati:', error);
        });
}

function disableBoughtItems(purchasedItems) {
    // Seleziona tutti i bottoni per l'acquisto
    const buttons = document.querySelectorAll('.buy-button');
    
    // Se ci sono bottoni
    if (buttons.length > 0) {
        let lastPurchasedIndex = -1; // Inizializza l'indice dell'ultimo acquisto

        buttons.forEach((button, index) => {
            const itemName = button.parentElement.querySelector('h3').textContent.trim();
            let result = itemName.split(' (')[0]; // Estrae il nome dell'oggetto senza parentesi

            // Se l'elemento è acquistato
            if (purchasedItems.includes(result)) {
                button.disabled = true;
                button.textContent = 'Bought';
                lastPurchasedIndex = index; // Salva l'indice dell'ultimo acquisto
            }
          
        });

        // Se esiste un bottone successivo rispetto all'ultimo acquistato
        if (lastPurchasedIndex >= 0 && lastPurchasedIndex < buttons.length - 1) {
            const nextButton = buttons[lastPurchasedIndex + 1];
            nextButton.disabled = false;
            nextButton.textContent = 'Buy Now'; // Modifica il testo o altre proprietà del bottone
        }

    } else {
        console.log('Nessun bottone trovato con la classe .buy-button');
    }
}


$(".component").on('click', function() {
    fetchPurchasedItems();
});





/*****************  LOGICA FARMING *************************** */
window.onload = async function  () {
    fetchPurchasedItems_INIT()
    const farmingButton = document.getElementById("farming-button");
    const userIdElement = document.getElementById("user-id");
    const PxDElement = document.getElementById("PxD");
    const balanceUser = document.getElementById("CNSL-point-mining");
    let farmingDuration = parseInt(PxDElement.textContent.trim(), 10) || 0;
    let farmingPoints = 0;
    let farmingStartTime = null;
    let farmingInProgress = false;
    let farmingInterval = null;


    

    // Funzione per aggiornare il progresso del farming
    function updateFarmingProgress() {
        if (!farmingInProgress) return;
    
        const now = Date.now();
        const elapsedSeconds = Math.floor((now - farmingStartTime) / 1000);
        let newFarmingProgress = (elapsedSeconds / farmingDuration) * 100;
        newFarmingProgress = Math.min(Math.max(newFarmingProgress, 0), 100);
        
       
        const element = document.getElementById('grid-component');

        // Aggiungi una nuova classe a ciascun elemento selezionato
       
        element.classList.add('disabled-component');
      

     // Verifica che il progresso venga aggiornato correttamente
        farmingButton.textContent = `Farming: ${newFarmingProgress.toFixed(2)}%`;
    
        const PxDValue = parseInt(PxDElement.textContent.trim().match(/\d+/)[0], 10);
        farmingPoints = Math.floor((newFarmingProgress / 100) * PxDValue);
    
        // Calcolo dei punti da ricevere
    
        // Quando il farming è completo
        if (newFarmingProgress === 100) {
            farmingButton.textContent = `Claim ${farmingPoints} $CNSL`;
            farmingButton.disabled = false;
            farmingButton.classList.add("farm-claim")
            farmingButton.classList.remove("farm-active")

            farmingButton.removeEventListener("click", startFarming);
            farmingButton.addEventListener("click", claimFarmingPoints);
            clearInterval(farmingInterval); // Ferma l'intervallo quando il farming è completato
        }
        else{
            const elements = document.querySelectorAll('.component');

            // Itera sugli elementi e rimuovi l'animazione
            elements.forEach(element => {
                element.style.animation = 'none';
            });

        }
    }
    

    function initializeButton() {
        const element = document.getElementById('grid-component');
        element.classList.remove('disabled-component');

        farmingButton.textContent = "Start Farm";
        farmingButton.disabled = false;
        farmingButton.addEventListener("click", startFarming);

    }
    
    function startFarming() {
        console.log("Start Farming clicked");
        const element = document.getElementById('grid-component');

        element.classList.add('disabled-component');

        const userId = userIdElement.textContent.trim();
    
        startFarmingAPI(userId)
        .then(data => {
            console.log("Farming started:", data);
    
            if (data.success) {
                farmingInProgress = true;
                farmingStartTime = Date.now();
                farmingDuration = data.farmingDuration;

                farmingPoints = 0;
    
                farmingButton.textContent = "Farming...";
                farmingButton.disabled = true;

                farmingButton.classList.add("farm-active")
                farmingButton.classList.remove("farm-claim")

    
                farmingInterval = setInterval(updateFarmingProgress, 10000);
                // Seleziona tutti gli elementi con la classe specificata
                const elements = document.querySelectorAll('.component');

                // Itera sugli elementi e rimuovi l'animazione
                elements.forEach(element => {
                    element.style.animation = 'none';
                });

    
                // Inizializza una verifica dello stato del farming a intervalli specifici
                setInterval(checkFarmingStatus, checkInterval);
            } else {
                console.error('Error:', data.error);
            }
        })
        .catch(error => {
            console.error('Error starting farming:', error);
        });
    }
    
    

    // Funzione per reclamare i punti
    function claimFarmingPoints() {
        const userId = userIdElement.textContent.trim();
        const farmingBalanceElement = document.getElementById("CNSL-point-mining");
    
        // Recupera il valore attuale del balance
        let currentBalance = parseInt(farmingBalanceElement.textContent.replace("$CNSL", "").trim(), 10) || 0;
    
        // Assicurati che farmingPoints sia un valore numerico valido
        const farmingPoints = document.getElementById('PxD').textContent.replace('PPD ', '').trim();


        claimFarmingPointsAPI(userId, farmingPoints)
        .then(data => {
            if (data.success) {
                // Modifica l'aspetto del bottone
                farmingButton.classList.remove("farm-active");
                farmingButton.classList.remove("farm-claim");
    
                // Somma i punti e aggiorna il balance
                currentBalance += parseInt(farmingPoints);
    
                // Aggiorna il DOM con il nuovo balance
                farmingBalanceElement.textContent = "$CNSL " + currentBalance;
    
                // Mostra il popup
                showPopup("$CNSL " + farmingPoints + " claimed!");
            }
    
            // Resetta lo stato del bottone dopo il claim
            initializeButton();
        })
        .catch(error => {
            console.error('Error claiming points:', error);
        });
    }
    

    // Funzione per controllare se esiste un farming in corso
    let lastCheckTime = 0;
    const checkInterval = 1800000; // Verifica lo stato ogni 60 secondi (60000 ms)

    function checkFarmingStatus() {
        const now = Date.now();
    
        if (now - lastCheckTime < checkInterval) {
            return;
        }
    
        lastCheckTime = now;
    
        const userId = userIdElement.textContent.trim();
        
        checkFarmingStatusAPI(userId)
            .then(data => {
                console.log('Farming status:', data);
    
                if (data.farmingStart) {
                    farmingInProgress = true;
                    farmingStartTime = new Date(data.farmingStart).getTime();
                    farmingDuration = data.farmingDuration;
    
                    if (data.claimed === 1) {
                        farmingButton.classList.remove("farm-active")
                        farmingButton.classList.remove("farm-claim")

                        // Se il farming è stato già reclamato
                        farmingButton.textContent = "Start Farming";
                        farmingButton.disabled = false;
                        farmingButton.removeEventListener("click", claimFarmingPoints);
                        farmingButton.addEventListener("click", startFarming);  // Il bottone ritorna al punto di partenza per iniziare un nuovo farming
                        clearInterval(farmingInterval); // Ferma l'intervallo se il farming è completato e il claim è stato fatto
                    } else if (data.progress >= 100 && data.claimed === 0) {
                        // Farming completato ma non ancora reclamato
                        const PxDValue = parseInt(PxDElement.textContent.trim().match(/\d+/)[0], 10);
                        
                        if (!isNaN(PxDValue) && PxDValue > 0) {
                            farmingButton.classList.add("farm-claim")

                            farmingPoints = Math.floor((data.progress / 100) * PxDValue);
                            farmingButton.textContent = `Claim ${farmingPoints} $CNSL`;
                            farmingButton.disabled = false;
                            farmingButton.removeEventListener("click", startFarming);
                            farmingButton.addEventListener("click", claimFarmingPoints);
                        } else {
                            console.error("Errore nel calcolo dei punti: PxDValue non valido");
                            farmingButton.textContent = "Error";
                            farmingButton.disabled = true;
                        }
                    } else if (data.progress < 100) {
                        // Farming in corso
                        farmingButton.textContent = `Farming: ${Math.floor(data.progress).toFixed(2)}%`;
                        farmingButton.disabled = true;
                        farmingButton.classList.add("farm-active")
                        farmingButton.classList.remove("farm-claim")

                        farmingInterval = setInterval(updateFarmingProgress, 100);
                    }
                } else {
                    initializeButton();
                }
            })
            .catch(error => {
                console.error('Error fetching farming status:', error);
                initializeButton();
            });
    }
    
    
    
    retrieve_user_info(parseInt(userIdElement.textContent)).then(response_ppd => {
        PxDElement.textContent = "PPD "+response_ppd.ppd_value
        balanceUser.textContent = "$CNSL "+response_ppd.balance
    });
    

    checkFarmingStatus();
};

