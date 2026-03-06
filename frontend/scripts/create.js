import { renderUsers } from './read.js';

export async function createUser() {
    const name = document.getElementById('name').value,
        age = document.getElementById('age').value,
        email = document.getElementById('email').value;

    await fetch('http://localhost:8000/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, age: Number(age), email }),
    });

    await renderUsers();
}