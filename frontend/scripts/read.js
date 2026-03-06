async function getUsers(apiUrl) {
    const response = await fetch(apiUrl);
    const data = await response.json();

    return data.users;
}

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