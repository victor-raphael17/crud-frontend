import { getUsers } from './utils/read.js';

export async function renderUsers(apiUrl) {
    const users = await getUsers(apiUrl);
    const usersSection = document.getElementById('users');

    usersSection.innerHTML = '';

    users.forEach(user => {
        const userDiv = document.createElement('div');

        userDiv.classList.add('col-md-3');

        userDiv.innerHTML = `
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">${user.name}</h5>
                    <p class="card-text mb-1"><strong>Age:</strong> ${user.age}</p>
                    <p class="card-text"><strong>Email:</strong> ${user.email}</p>
                </div>
            </div>
        `;

        usersSection.appendChild(userDiv);
    });
}