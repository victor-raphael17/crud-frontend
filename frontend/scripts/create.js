import { renderUsers } from './render.js';

export async function createUser(apiUrl) {
    const name = document.getElementById('name').value,
        age = document.getElementById('age').value,
        email = document.getElementById('email').value;

    await fetch(apiUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, age: Number(age), email }),
    });

    await renderUsers(apiUrl);
}