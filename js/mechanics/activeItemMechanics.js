import { keys } from '../utils/update-level.js';
import { updateUserBalance } from '../api/api-update-balance.js';
import { getActiveItem } from '../api/update-active-item.js';
import { onDomReadyBuyItem } from '../events/domReadyActiveItem.js';

export function disattivaKeys(currentItemId) {
    document.querySelectorAll('.menu-keys-item').forEach(item => {
        const itemId = Number(item.dataset.id);
        if (itemId <= currentItemId) {
            item.parentElement.classList.add('key_disabled');
            item.classList.add('key_disabled');
        } else if (itemId == currentItemId + 1) {
            item.parentElement.classList.remove('key_disabled');
            item.classList.remove('key_disabled');
        } else {
            item.parentElement.classList.add('key_disabled');
            item.classList.add('key_disabled');
        }
    });
}

export function decrementaSaldo(userId, itemId) {
    const numericItemID = Number(itemId);
    const point_to_add = parseInt(document.getElementById('CNSL-point').textContent);
    const item = keys.find(key => key.id === numericItemID);
    if (item) {
        const amount_item = item.cost;
        updateUserBalance(userId, amount_item, "purchase", point_to_add);
    } else {
        console.error("Item con id", itemId, "non trovato.");
    }
}

export function showPopupKey(userId) {
    const h2Element = document.getElementById("CNSL-point");
    const textValue = h2Element.textContent;
    const numericString = textValue.replace(/\./g, "");
    let userBalance = parseInt(numericString, 10);

    const popup = document.getElementById('popup');
    popup.style.display = 'block';
    
    const popupItems = document.getElementById('popup-items');
    popupItems.innerHTML = '';
    
    keys.forEach(item => {
        const itemDiv = document.createElement('div');
        itemDiv.classList.add('popup-item');
        itemDiv.innerHTML = `<div class="container-img-item"><img src="${item.img}" class="menu-keys-item" data-id="${item.id}" id="item-${item.id}"></div><p class='price-button ${item.purchased ? 'price-button-buy' : 'price-button-bought'}'>Price: ${item.cost}</p><p class='claim-push'>Push: ${item.claim}</p>`;
        popupItems.appendChild(itemDiv);
    });

    getActiveItem(userId).then(response => {
        console.log("API Success:", response);
        if (response.success) {
            const activeItemId = response.item_id;
            disattivaKeys(activeItemId);
            onDomReadyBuyItem(); // Aggiungi i listener per gli acquisti dopo aver disattivato gli item
        } else {
            console.error('Error getting active item:', response.error);
        }
    }).catch(error => console.error('API Error:', error));
}

export function closePopup() {
    const popup = document.getElementById('popup');
    popup.style.display = 'none';
}
