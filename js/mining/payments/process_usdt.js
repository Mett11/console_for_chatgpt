import { showPopup } from "../../utils/showPopup.js";
import TonWeb from "https://esm.sh/tonweb";

export async function processUsdtPayment(itemData) {
    return new Promise(async (resolve, reject) => {
        try {
            if (!window.tonWallet || !window.tonWallet.account || !window.tonWallet.account.address) {
                showPopup("Please connect your TON wallet to complete the payment.");
                return reject("Error: Wallet not connected");
            }

            if (!window.tonConnectUI) {
                showPopup("TON Connect is not initialized.");
                return reject("Error: TON Connect is not initialized");
            }

            const friendlyAddress_testnet = "0QA6dCK-Py8vA4YSN-syHiNtEA51pl-4lR3BdwNXBbVFVGog";
            const friendlyAddress ="UQBa3taT8E1gY3mo7QdeIT6a7RWRpGAf3pCS6mbQm0Rn9xcB"
            const addressObj = new TonWeb.utils.Address(friendlyAddress);
            const rawAddress = addressObj.toString(false, true, true);

            const now = Math.floor(Date.now() / 1000);
            const transactionData = {
                validUntil: now + 300, // 5 minuti invece di 1 minuto
                messages: [
                    {
                        address: rawAddress,
                        amount: (itemData.price_usdt * 1e9).toString(),
                    }
                ]
            };


            showPopup("Processing transaction...");

            let txResult;
            try {
                txResult = await window.tonConnectUI.sendTransaction(transactionData);
            } catch (error) {
                return reject(error.message);
            }

            if (txResult && (txResult.transactionHash || txResult.boc)) {
                showPopup("Payment successful!");
                return resolve("Payment successful!");
            } else {
                return reject("Payment failed. Please try again.");
            }
        } catch (error) {
            showPopup("An error occurred during the payment process.");
            return reject(error.message);
        }
    });
}
