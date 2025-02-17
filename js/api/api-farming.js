import { getCommonHeaders } from '../utils/manage-header.js'; // Importa la funzione per ottenere gli headers comuni

export async function getPurchaseItems(userId) {
    const response = await fetch(`back-end/mining/get-purchased.php?user_id=${userId}`, {
        headers: getCommonHeaders() // Usa gli headers comuni
    });
    return await response.json();
}

export async function startFarmingAPI(userId) {
    const response = await fetch('back-end/mining/start-farming.php', {
        method: 'POST',
        headers: getCommonHeaders(),
        body: JSON.stringify({ userId: userId })
    });
    return await response.json();
}

export async function claimFarmingPointsAPI(userId, farmingPoints) {
    const response = await fetch('back-end/mining/claim-farming.php', {
        method: 'POST',
        headers: getCommonHeaders(),
        body: JSON.stringify({
            userId: userId,
            claimedPoints: farmingPoints
        })
    });
    return await response.json();
}


export async function checkFarmingStatusAPI(userId) {
    const response = await fetch(`back-end/mining/farming-status.php?userId=${userId}`, {
        headers: getCommonHeaders() // Usa gli headers comuni
    });
    return await response.json();
}


export async function fetchMiningComponents() {
    const response = await fetch('js/mining/mining-components.json');
    return await response.json();
}

export async function savePurchase(userId, itemData, typeHW) {
    const response = await fetch('back-end/mining/save-purchase.php', {
        method: 'POST',
        headers: getCommonHeaders(),
        body: JSON.stringify({
            userId: userId,
            hardwareType: typeHW,
            hardwareName: itemData.name,
            priceCNS: itemData.price_cns,
            priceUSDT: itemData.price_usdt,
            miningProfit: itemData.mining_profit_cns,
            hardwareLevel: itemData.level,
        })
    });
    return await response.json();

}
