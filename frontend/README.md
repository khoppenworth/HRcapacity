# HRassess v4 Staff Frontend

## Prerequisites
- Node.js 18+
- npm or yarn

## Installation
Install dependencies:
```bash
npm install
```

## Configuration
Copy `.env.example` to `.env` (if needed) and set the API base URL. By default the app points to `http://localhost:8000/api/v1`.

Create `frontend/.env` with:
```
VITE_API_URL=http://localhost:8000/api/v1
```

## Development Server
Run the Vite dev server:
```bash
npm run dev
```
The application is available at `http://localhost:5173`.

## Build
Create a production build:
```bash
npm run build
```

## Preview Production Build
```bash
npm run preview
```
