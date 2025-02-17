import { getCommonHeaders } from '../utils/manage-header.js'; // Importa la funzione per gli header


export async function getActiveItem(userId) {
    const response = await fetch('back-end/get-active-item.php', {
        method: 'POST',
        headers: getCommonHeaders(),

        body: JSON.stringify({ user_id: userId })
    });
    return response.json();
}

export async function insertPurchase(userId, src_keys, purchased, itemId) {
    const response = await fetch('back-end/insert-purchase.php', {
        method: 'POST',
        headers: getCommonHeaders(),

        body: JSON.stringify({
            user_id: userId,
            src: src_keys,
            purchased: purchased,
            item_id: itemId
        })
    });
    return response.json();
}
