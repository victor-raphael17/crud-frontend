import { getUsers } from './read.js';

export async function renderUsers(apiUrl) {
    const users = await getUsers(apiUrl);
    const usersSection = document.getElementById('users');

    usersSection.innerHTML = '';

    users.forEach(user => {
        const userDiv = document.createElement('div');

        userDiv.classList.add('user');

        userDiv.innerHTML = `
            <p>Name: ${user.name}</p>
            <p>Age: ${user.age}</p>
            <p>Email: ${user.email}</p>
        `;

        usersSection.appendChild(userDiv);
    });
}