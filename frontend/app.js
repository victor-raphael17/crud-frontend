import { renderUsers } from './scripts/dom/render.js';
import { createUser } from './scripts/api/create.js';

const apiUrl = 'http://localhost:8000/api/users';

const form = document.getElementById('create-user-form');

document.addEventListener('DOMContentLoaded', () => {
    renderUsers(apiUrl);
});

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const name = document.getElementById('name').value;
    const age = document.getElementById('age').value;
    const email = document.getElementById('email').value;

    await createUser(apiUrl, { name, age, email });
    await renderUsers(apiUrl);
    form.reset();
});
