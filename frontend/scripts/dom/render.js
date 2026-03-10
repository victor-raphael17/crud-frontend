import { getUsers } from '../api/read.js';

export async function renderUsers(apiUrl, { onEdit, onDelete } = {}) {
    const users = await getUsers(apiUrl);
    const usersSection = document.getElementById('users');

    if (users.length === 0) {
        usersSection.innerHTML = '<p class="text-muted">No users found.</p>';
        return;
    }

    usersSection.innerHTML = '';

    users.forEach((user) => {
        const userDiv = document.createElement('div');
        userDiv.classList.add('col-md-3');

        userDiv.innerHTML = `
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">${user.name}</h5>
                    <p class="card-text mb-1"><strong>Age:</strong> ${user.age}</p>
                    <p class="card-text"><strong>Email:</strong> ${user.email}</p>
                </div>
                <div class="card-footer d-flex gap-2">
                    <button class="btn btn-sm btn-outline-dark flex-fill btn-edit">Edit</button>
                    <button class="btn btn-sm btn-outline-danger flex-fill btn-delete">Delete</button>
                </div>
            </div>
        `;

        userDiv.querySelector('.btn-edit').addEventListener('click', () => {
            onEdit?.(user.id, user);
        });

        userDiv.querySelector('.btn-delete').addEventListener('click', () => {
            onDelete?.(user.id, user);
        });

        usersSection.appendChild(userDiv);
    });
}
