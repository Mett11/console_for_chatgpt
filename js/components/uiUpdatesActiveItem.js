import { getActiveItem } from '../api/update-active-items.js';
import { keys } from '../utils/update-level.js';
import { disattivaKeys } from '../mechanics/activeItemMechanics.js';

export function showPopup(userId) {
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
        itemDiv.innerHTML = `<img src="${item.img}" class="menu-keys-item" data-id="${item.id}" id="item-${item.id}"><p class='price-button ${item.purchased ? 'price-button-buy' : 'price-button-bought'}'>Price: ${item.cost}</p><p class='claim-push'>Push: ${item.claim}</p>`;
        popupItems.appendChild(itemDiv);
    });

    getActiveItem(userId).then(response => {
        console.log("API Success:", response);
        if (response.success) {
            const activeItemId = response.item_id;
            disattivaKeys(activeItemId);
        } else {
            console.error('Error getting active item:', response.error);
        }
    }).catch(error => console.error('API Error:', error));
}

export function closePopup() {
    const popup = document.getElementById('popup');
    popup.style.display = 'none';
}
