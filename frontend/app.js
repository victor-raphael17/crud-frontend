import { renderUsers } from './scripts/dom/render.js';
import { createUser } from './scripts/api/create.js';
import { updateUser } from './scripts/api/update.js';
import { deleteUser } from './scripts/api/delete.js';

const apiUrl = 'http://localhost:8000/api/users';

const form = document.getElementById('create-user-form');
const formTitle = document.getElementById('form-title');
const submitBtn = form.querySelector('button[type="submit"]');
const cancelBtn = document.getElementById('cancel-edit');

let editingIndex = null;

function enterEditMode(index, user) {
    editingIndex = index;
    document.getElementById('name').value = user.name;
    document.getElementById('age').value = user.age;
    document.getElementById('email').value = user.email;
    formTitle.textContent = 'Edit User';
    submitBtn.textContent = 'Update';
    cancelBtn.style.display = '';
    document.getElementById('name').focus();
}

function exitEditMode() {
    editingIndex = null;
    formTitle.textContent = 'Create User';
    submitBtn.textContent = 'Create';
    cancelBtn.style.display = 'none';
    form.reset();
}

function refreshUsers() {
    renderUsers(apiUrl, {
        onEdit: enterEditMode,
        onDelete: async (index) => {
            if (!confirm('Are you sure you want to delete this user?')) return;

            try {
                deleteUser(apiUrl, index);
                if (editingIndex === index) exitEditMode();
                refreshUsers();
            } catch (error) {
                alert(error.message);
            }
        },
    });
}

cancelBtn.addEventListener('click', exitEditMode);

document.addEventListener('DOMContentLoaded', refreshUsers);

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const name = document.getElementById('name').value;
    const age = document.getElementById('age').value;
    const email = document.getElementById('email').value;

    try {
        if (editingIndex !== null) {
            updateUser(apiUrl, editingIndex, { name, age, email });
        } else {
            createUser(apiUrl, { name, age, email });
        }

        exitEditMode();
        refreshUsers();
    } catch (error) {
        alert(error.message);
    }
});
