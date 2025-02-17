export function showPopup(message) {
    // Creazione dell'overlay
    const overlay = document.createElement('div');
    overlay.style.position = 'fixed';
    overlay.style.top = '0';
    overlay.style.left = '0';
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
    overlay.style.zIndex = '999'; // Posizionamento dietro al popup
    overlay.style.display = 'flex';
    overlay.style.justifyContent = 'center';
    overlay.style.alignItems = 'center';

    // Creazione del popup
    const popup = document.createElement('div');
    popup.textContent = message;
    popup.style.backgroundColor = '#fff';
    popup.style.color = '#000';
    popup.style.padding = '15px 25px';
    popup.style.borderRadius = '10px';
    popup.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.2)';
    popup.style.fontSize = '16px';
    popup.style.textAlign = 'center';
    popup.style.zIndex = '1000'; // Posizionamento sopra l'overlay
    popup.style.animation = 'fadeOut 0.5s ease-out 3s forwards';

    // Aggiunta del popup all'overlay
    overlay.appendChild(popup);

    // Aggiunta dell'overlay al body
    document.body.appendChild(overlay);

    // Rimozione del popup e dell'overlay dopo 3.5 secondi
    setTimeout(() => {
        overlay.remove();
    }, 3500);
}

/**
 * Mostra una finestra di dialogo personalizzata per la conferma.
 * @param {string} message - Il messaggio da mostrare.
 * @returns {Promise<boolean>} - Promise che si risolve con true se l'utente conferma, false altrimenti.
 */
export function customConfirm(message) {
    return new Promise((resolve) => {
      // Crea l'elemento modale
      const modal = document.createElement('div');
      modal.className = 'custom-modal';
      modal.innerHTML = `
        <div class="custom-modal-content">
          <p>${message}</p>
          <div class="custom-modal-buttons">
            <button class="btn-confirm">Yes</button>
            <button class="btn-cancel">No</button>
          </div>
        </div>
      `;
      
      // Aggiungi il modale al body
      document.body.appendChild(modal);
  
      // Gestisci il click sul pulsante "Yes"
      modal.querySelector('.btn-confirm').addEventListener('click', () => {
        resolve(true);
        document.body.removeChild(modal);
      });
      // Gestisci il click sul pulsante "No"
      modal.querySelector('.btn-cancel').addEventListener('click', () => {
        resolve(false);
        document.body.removeChild(modal);
      });
    });
  }
  