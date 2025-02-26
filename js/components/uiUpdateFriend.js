import { getFriends } from '../api/get-friend.js';

export async function displayFriends(userId) {
    try {
        const friends = await getFriends(userId);
        const friendsList = document.getElementById("friends-list");
        const totalPointElement = document.getElementById("total_point_ref");  // Elemento per i punti totali

        friendsList.innerHTML = ""; // Pulizia della lista

        // Aggiungi ogni amico alla lista
        friends.forEach(friend => {
            const listItem = document.createElement("li");
            
            const id_friend = document.createElement("h1");
            id_friend.classList.add("id_friend");
            
            const point_friend = document.createElement("h1");
            point_friend.classList.add("point_friend");
            
            id_friend.textContent = `Friend: ${friend.invited_id}`;
            point_friend.textContent = "3000 $CNSL";

            listItem.append(id_friend);
            listItem.append(point_friend);
            friendsList.appendChild(listItem);
        });

        // Calcola il totale dei punti
        const totalPoints = friends.length * 3000;  // 1000 punti per ogni amico invitato
        totalPointElement.textContent = `${totalPoints} $CNSL`;  // Aggiorna il valore dell'elemento

        // Crea il contatore dinamicamente e posizionalo dopo la lista
        const invitedCountElement = document.createElement("p");
        invitedCountElement.classList.add("number-invited-user");

        invitedCountElement.innerHTML = `<span id='header_invited_user'> Invited Users:</span> <span id="invited-count">${friends.length}/15</span>`;
        
        // Inserisce il contatore dopo la lista degli amici
        friendsList.insertAdjacentElement('afterend', invitedCountElement);

        // Gestisci la visibilità della freccia
        const arrowElement = document.querySelector('.arrow-bottom-friends');
        if (friends.length > 10 && arrowElement) {
            arrowElement.style.display = 'block'; // Mostra l'elemento .arrow-bottom-friends
        } else if (arrowElement) {
            arrowElement.style.display = 'none'; // Nascondi l'elemento .arrow-bottom-friends
        }

    } catch (error) {
        console.error('Errore nel recupero degli amici:', error.message);
    }
}