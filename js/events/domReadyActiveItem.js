import { getActiveItem, insertPurchase } from '../api/update-active-item.js';
import { keys } from '../utils/update-level.js';
import { showPopupKey, closePopup, disattivaKeys, decrementaSaldo } from '../mechanics/activeItemMechanics.js';
import { showPopup, customConfirm } from '../utils/showPopup.js';
export function onDomReadyActiveItem() {
    document.addEventListener('DOMContentLoaded', () => {
        console.log("DOMContentLoaded event fired");

        const userId = parseInt(document.getElementById('user-id').textContent);
        getActiveItem(userId).then(response => {
            console.log("API Success:", response);
            if (response.success) {
                const activeItemId = response.item_id;
                const activeItem = keys.find(i => i.id === activeItemId);
                if (activeItem) {
                    const userKeysElement = document.getElementById('user-keys');
                    userKeysElement.src = response.img_path_user_keys;
                    userKeysElement.dataset.id = response.item_id;
                    disattivaKeys(activeItemId);
                } else {
                    console.log('Item not found for ID:', activeItemId);
                }
            } else {
                console.error('Error getting active item:', response.error);
            }
        }).catch(error => console.error('API Error:', error));

        document.getElementById('open-keys').addEventListener('click', () => showPopupKey(userId));
        document.getElementById('close-popup').addEventListener('click', closePopup);
    });
}

export function onDomReadyBuyItem() {
    const items = document.querySelectorAll('.menu-keys-item:not(.key_disabled)');
    console.log(items); // Verifica se gli elementi vengono selezionati
  
    items.forEach(item => {
      item.addEventListener('click', () => {
        customConfirm("Are you sure to buy this key?")
          .then(confirmed => {
            if (!confirmed) return; // Se l'utente clicca "No", interrompi qui
  
            const userId = parseInt(document.getElementById('user-id').textContent);
            const itemId = item.dataset.id;
            // Rimuove "Console_2/" dal percorso dell'immagine
            const src_keys = item.src
              .replace(window.location.origin + '/', '')
              .replace('Console_2/', '');
            console.log('src_keys:', src_keys); // Verifica il valore di src_keys
  
            const itemObj = keys.find(src => src.img === src_keys);
            console.log('itemObj:', itemObj); // Verifica cosa viene trovato
  
            if (!itemObj) {
              console.error('Item not found in keys:', src_keys);
              return; // Uscita anticipata se l'item non viene trovato
            }
            
            const purchased = 1;
            document.getElementById('user-id').dataset.id = itemId;
            const UserBalance = Number(document.getElementById('CNSL-point').textContent);
            
            if (UserBalance >= itemObj.cost) {
              insertPurchase(userId, src_keys, purchased, itemId)
                .then(response => {
                  if (response.success) {
                    const new_src = response.new_src;
                    const userKeysElement = document.getElementById('user-keys');
                    userKeysElement.src = new_src;
                    userKeysElement.dataset.id = response.item_id;
                    disattivaKeys(Number(itemId));
                    decrementaSaldo(userId, Number(itemId));
                    showPopup("Key purchased!");
                  } else {
                    showPopup("Error during the purchase :(");
                    console.error('Error inserting purchase:', response.error);
                  }
                })
                .catch(error => console.error('API Error:', error));
            } else {
              showPopup("Insufficient balance");
            }
          });
      });
    });
  }
  
