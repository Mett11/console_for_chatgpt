import { getCommonHeaders } from '../utils/manage-header.js'; // Importa la funzione per gli header


export const getTasks = async (type) => {
    const response = await fetch(`back-end/task/get-task.php?type=${type}`,{
        headers: getCommonHeaders(),
    });
    if (!response.ok) {
        throw new Error("Errore nella risposta dal server");
    }
    return await response.json();
};

export const checkTaskCompletion = async (userId) => {
    const response = await fetch(`back-end/task/check-task-completion.php?user_id=${userId}`,{
        headers: getCommonHeaders(),
    });
    return await response.json();
};

export const claimTaskApi = async (userId, taskId) => {
    const response = await fetch('back-end/task/claim-task.php', {
        method: 'POST',
        headers: getCommonHeaders(),
        body: JSON.stringify({ user_id: userId, task_id: taskId })
    });
    return await response.json();
};

export const getUserBalance =  async (userId) => {
    const response = await fetch(`back-end/get-balance.php?user_id=${userId}`, {
        method: 'GET',
        headers: getCommonHeaders(),

        });
    return await response.json();
};

export const getDailyClaims = async (userId, taskId, taskType) => {
    const response = await fetch(`back-end/task/get-daily-claim.php?user_id=${userId}&task_type=${taskType}&task_id=${taskId}`,{
        method: 'GET',
        headers: getCommonHeaders(),

        });
    return await response.json();
};
