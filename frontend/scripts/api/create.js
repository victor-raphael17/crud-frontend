export async function createUser(apiUrl, { name, age, email }) {
    await fetch(apiUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, age: Number(age), email }),
    });
}
