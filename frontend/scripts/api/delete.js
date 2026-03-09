export async function deleteUser(apiUrl, index) {
    const response = await fetch(`${apiUrl}?index=${index}`, {
        method: 'DELETE',
    });

    const data = await response.json();

    if (!response.ok) {
        throw new Error(data.error || 'Failed to delete user');
    }

    return data;
}
