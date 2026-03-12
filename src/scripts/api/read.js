export async function getUsers(apiUrl) {
    const response = await fetch(apiUrl);
    const data = await response.json();

    return data.users;
}