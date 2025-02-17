// event-handler.js
import { displayFriends } from '../components/uiUpdateFriend.js'


export function onDomReadyFriend() {
    document.addEventListener('DOMContentLoaded', () => {
        const user_id = parseInt($("#user-id").text())
        displayFriends(user_id);
    });
}
