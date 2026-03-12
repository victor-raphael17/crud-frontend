export async function deleteUser(apiUrl, id) {
    const response = await fetch(`${apiUrl}?id=${id}`, {
        method: 'DELETE',
    });

    const data = await response.json();

    if (!response.ok) {
        throw new Error(data.error || 'Failed to delete user');
    }

    return data;
}