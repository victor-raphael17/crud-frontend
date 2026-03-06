# CRUD JS Project

A simple CRUD application with a vanilla JavaScript frontend and a PHP API backend.

## Project Structure

```
crud-js-project/
├── backend/
│   └── api.php            # PHP API endpoint
├── frontend/
│   ├── pages/
│   │   ├── index.html     # Main page
│   │   └── app.js         # Entry point (imports and event listeners)
│   ├── scripts/
│   │   ├── read.js        # Fetches and renders users
│   │   └── create.js      # Creates a new user via the API
│   └── styles/
│       ├── reset.css
│       └── style.css
├── data/
│   └── data.json          # Users data store
└── index.html             # Landing page with navigation
```

## Prerequisites

- PHP 7.4+ installed on your machine

## How to Start

1. Start the PHP API server:

```bash
cd backend
php -S localhost:8000
```

2. Open `frontend/pages/index.html` in your browser.

That's it. The frontend communicates with the PHP API at `http://localhost:8000/api.php`, which reads and writes to `data/data.json`.

## API Endpoints

| Method | URL       | Description              |
|--------|-----------|--------------------------|
| GET    | /api.php  | Returns all users        |
| POST   | /api.php  | Creates a new user       |

### POST body example

```json
{
  "name": "John Doe",
  "age": 30,
  "email": "johndoe@gmail.com"
}
```
