# Social App Lab

A Twitter-like social application built with Laravel (API) and Nuxt (SPA).

## Demo

- Frontend: https://social-app-lab.vercel.app

Demo Account
- Email: demo@example.com
- Password: password

---

## Overview

This is a full-stack social application with core SNS features such as posting, commenting, liking, bookmarking, reposting, following, and notifications.

It is designed with a focus on API structure and data relationships.

---

## Tech Stack

### Backend
- Laravel 11
- Laravel Sanctum (Authentication)
- PostgreSQL / SQLite

### Frontend
- Nuxt 4
- TypeScript
- Tailwind CSS

### Infrastructure
- Vercel (Frontend)
- Railway (Backend & Database)

---

## Features

- Post / Comment system (with thread support)
- Like / Bookmark / Repost
- Follow system
- Media upload (posts & comments)
- User profile (posts / comments / liked / media)
- Search (posts / users / topics)
- Notification system

---

## Notifications

- Paginated notification list
- Unread count
- Mark all as read
- Includes related data (actor / post / comment)

---

## ER Diagram

- [er-main.svg](./er-main.svg)
- [er-notifications.svg](./er-notifications.svg)

---

## Setup

### Backend

```bash
cd api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### Frontend

```bash
cd web
npm install
npm run dev
```

---

## Deployment
- Frontend: Vercel
- Backend: Railway

## Notes
- Implemented realistic SNS features such as notifications, media handling, and threaded comments
- Designed API with clear relationships between entities
- Focused on handling user state (likes, bookmarks, follows) consistently between frontend and backend