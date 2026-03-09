export async function updateUser(apiUrl, index, { name, age, email }) {
    const response = await fetch(`${apiUrl}?index=${index}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, age: Number(age), email }),
    });

    const data = await response.json();

    if (!response.ok) {
        throw new Error(data.error || 'Failed to update user');
    }

    return data;
}
