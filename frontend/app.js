import { renderUsers } from './scripts/read.js';
import { createUser } from './scripts/create.js';

const apiUrl = 'http://localhost:8000/api';

const form = document.getElementById('create-user-form');

document.addEventListener('DOMContentLoaded', () => {
    renderUsers(apiUrl);
});

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    await createUser(apiUrl);
    form.reset();
});