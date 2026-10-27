# CAGAD website

The public site for the Centre for Advancement of Governance and Development, plus the backend that powers its blog.

```
cagad/
├── src/, public/, index.html   React + Vite site (deployed to Vercel at cagad.org)
└── backend/                    Laravel 13 app: blog admin panel + read-only JSON API
```

Editors write posts in the Laravel admin panel. The React site reads them from the API and shows them at `/blog`, `/blog/:slug` and in the "Latest Insights" section on the homepage. Every image (cover images and images inside posts) is uploaded to **Cloudinary**; nothing is stored on the server.

## Run it locally

You need PHP 8.3+, Composer and Node 20+.

**1. Backend** (http://localhost:8000)

```bash
cd backend
composer setup                 # installs, creates .env, migrates, seeds the admin, categories and sample posts
# put the Cloudinary credentials in backend/.env (see backend/README.md)
php artisan cloudinary:check   # confirms the credentials work
php artisan serve
```

Sign in at http://localhost:8000/admin as `admin@cagad.org` with the password `password` (the seeded super admin; change the password under **My account**).

**2. Frontend** (http://localhost:5173)

```bash
npm install
npm run dev
```

In development the site talks to `http://localhost:8000` automatically. To point it somewhere else, copy `.env.example` to `.env.local` and set `VITE_API_URL`.

## Deploying

**Frontend (Vercel).** Add an environment variable `VITE_API_URL` with the backend's public address (e.g. `https://api.cagad.org`), then redeploy. `vercel.json` rewrites every path to `index.html` so `/blog/...` URLs work when opened directly or refreshed.

**Backend.** Any PHP 8.3+ host works (Laravel Cloud, Forge, a VPS, or cPanel with the document root set to `backend/public`). See [backend/README.md](backend/README.md) for the environment variables and the post-deploy commands.

Deploy the backend before (or together with) the frontend changes. Until `VITE_API_URL` points at a live backend, the Blog page shows a "couldn't load" message, and the homepage simply hides the Latest Insights section.

## Scripts

| Where | Command | What it does |
| --- | --- | --- |
| root | `npm run dev` / `build` / `lint` | Vite dev server, production build, ESLint |
| backend | `composer dev` | Laravel server, queue worker and log viewer together |
| backend | `php artisan test` | Backend test suite |
| backend | `vendor/bin/pint` | PHP code style |
