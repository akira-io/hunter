<div align="center">

# 🚀 Hunter

### *The Cape Verdean Network for Developers*

**Where code meets community. Build, share, and grow together.**

[![Tests](https://github.com/akira-io/devhunter/actions/workflows/tests.yml/badge.svg)](https://github.com/akira-io/devhunter/actions)
[![Latest Release](https://img.shields.io/github/v/release/akira-io/devhunter?style=flat-square&color=blue)](https://github.com/akira-io/devhunter/releases)
[![Open Issues](https://img.shields.io/github/issues/akira-io/devhunter?style=flat-square&color=green)](https://github.com/akira-io/devhunter/issues)
[![License](https://img.shields.io/github/license/akira-io/devhunter?style=flat-square&color=orange)](https://github.com/akira-io/devhunter/blob/main/LICENSE.md)
[![Discord](https://img.shields.io/badge/Discord-Join%20Us-5865F2?style=flat-square&logo=discord&logoColor=white)](https://discord.gg/ghPqZg3RcZ)

[Features](#-features) • [Documentation](#-documentation) • [Quick Start](#-quick-start) • [Tech Stack](#-tech-stack) • [Contributing](#-contributing)

---

![Dev Hunter Platform](/public/img.png)

![User Profile Interface](public/img_1.png)

![Social Feed](public/img_2.png)

</div>

---

## 📋 Table of Contents

- [🎯 Overview](#-overview)
- [✨ Features](#-features)
- [🚀 Quick Start](#-quick-start)
- [📚 Documentation](#-documentation)
- [🛠️ Tech Stack](#-tech-stack)
- [🗺️ Roadmap](#-roadmap)
- [🤝 Contributing](#-contributing)
- [📬 Community](#-community)
- [📄 License](#-license)

---

## 🎯 Overview

**Hunter** is a social network built by developers, for developers. It's a place where you can showcase your skills,
share your coding journey, connect with like-minded professionals, and build meaningful relationships in the tech
community.

### Why  Hunter?

The developer community needed a platform that truly understands their needs:

- **👤 Beyond Code**: Portfolios are static. GitHub is technical but impersonal. We bring the human element to your
  professional identity.
- **💬 Real Connections**: A space for devs to share ideas, thoughts, and reflections—not just code.
- **🌱 Community Growth**: Build genuine relationships and collaborate with developers who share your passions.
- **🎯 Developer-First**: No hidden algorithms, no corporate interests—just a community built for developers.

---

## ✨ Features

<table>
<tr>
<td width="50%">

### 🎨 Rich Developer Profiles

Create a comprehensive profile that showcases who you are:

- **Skills & Tech Stack** display
- **Academic Background** and experience
- **Portfolio Integration** (GitHub, LinkedIn, etc.)
- **Custom Avatars & Banners**

</td>
<td width="50%">

### 📝 Hunts (Posts)

Share your developer journey:

- **Devlogs & Updates** documentation
- **Code Snippets** and solutions
- **Media Support** for images
- **Engagement** with likes and comments

</td>
</tr>
<tr>
<td width="50%">

### 🤝 Social Features

Build your developer network:

- **Follow System** to connect
- **Real-time Notifications**
- **Comments & Discussions**
- **Personalized Feed**

</td>
<td width="50%">

### 💬 Real-time Chat

Collaborate instantly:

- **WebSocket-Powered** messaging
- **Private Conversations**
- **Read Receipts**
- **Online Presence** indicators

</td>
</tr>
</table>

### 🔍 Smart Discovery

- **Full-text Search** powered by Meilisearch
- **Skill-based Discovery** to find developers by tech stack
- **Location Filters** for local connections
- **Personalized Recommendations**

---

## 🚀 Quick Start

### Prerequisites

- **PHP** 8.4+
- **Composer** 2.x
- **Node.js** 18+
- **PostgreSQL** 14+
- **Redis** (for cache & queues)

### Installation

```bash
# Clone the repository
git clone https://github.com/akira-io/devhunter.git
cd devhunter

# Install dependencies
composer install
npm install

# Set up environment
cp .env.example .env
php artisan key:generate

# Configure your database in .env (PostgreSQL recommended)
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_DATABASE=devhunter

# Run migrations
php artisan migrate

# Start the development server
php artisan serve

# In another terminal, start Vite
npm run dev
```

### Alternative Setup Methods

<details>
<summary><b>🐳 Using Docker (Laravel Sail)</b></summary>

```bash
# Install Sail
composer require laravel/sail --dev

# Start containers
./vendor/bin/sail up -d

# Run migrations
./vendor/bin/sail artisan migrate
```

</details>

<details>
<summary><b>🦌 Using Laravel Herd</b></summary>

1. Install [Laravel Herd](https://herd.laravel.com)
2. Link your project directory
3. Access at `https://devhunter.test`
4. No need to run `php artisan serve`

</details>

> 📖 **For detailed installation instructions**, see the [Getting Started Guide](./docs/01-getting-started.md)

---

## 📚 Documentation

**📖 Comprehensive documentation is available in English in the [`/docs`](./docs) folder!**

All documentation includes practical examples and is optimized for production use with PostgreSQL.

### 📘 Documentation Index

| Category               | Documents                                                 | Description                     |
|------------------------|-----------------------------------------------------------|---------------------------------|
| **🚀 Getting Started** | [Installation Guide](./docs/01-getting-started.md)        | Complete setup instructions     |
|                        | [Quick Reference](./docs/QUICK-REFERENCE.md)              | Common commands & examples      |
| **🔐 Core Features**   | [Authentication](./docs/02-authentication.md)             | Login, OAuth, security          |
|                        | [User Profiles](./docs/03-user-profiles.md)               | Profile management              |
|                        | [Hunts (Posts)](./docs/04-hunts.md)                       | Creating and managing posts     |
|                        | [Social Features](./docs/05-social-features.md)           | Follow, like, comment           |
| **💬 Real-time**       | [Real-time Chat](./docs/06-real-time-chat.md)             | WebSocket chat system           |
|                        | [Advanced Chat](./docs/13-advanced-chat-features.md)      | Reply, edit, reactions, search  |
| **🔍 Discovery**       | [Search & Discovery](./docs/07-search-discovery.md)       | Meilisearch integration         |
| **🔔 Engagement**      | [Notifications](./docs/11-notifications-system.md)        | Real-time & email notifications |
|                        | [Gamification](./docs/12-gamification-system.md)          | XP, Levels, Badges, Rewards     |
| **🔒 Security**        | [Privacy & Security](./docs/14-privacy-security.md)       | Privacy controls & 2FA          |
| **🛠️ Development**    | [API Reference](./docs/08-api-reference.md)               | Complete API documentation      |
|                        | [Frontend Development](./docs/09-frontend-development.md) | React, TypeScript, Inertia      |
|                        | [Testing](./docs/10-testing.md)                           | Pest PHP testing guide          |
|                        | [Deployment](./docs/15-deployment-production.md)          | Production deployment guide     |

> 💡 **Start here**: New developers should begin with the [Getting Started Guide](./docs/01-getting-started.md)

---

## 🛠️ Tech Stack

### Backend

<table>
<tr>
<td>

**Framework & Core**

- Laravel 12
- PHP 8.4+
- PostgreSQL 14+
- Redis

</td>
<td>

**Real-time & Search**

- Laravel Reverb (WebSockets)
- Laravel Scout
- Meilisearch
- Laravel Sanctum (API Auth)

</td>
</tr>
</table>

### Frontend

<table>
<tr>
<td>

**UI & Styling**

- React 18
- TypeScript
- Tailwind CSS v4
- Vite

</td>
<td>

**State & Navigation**

- Inertia.js v2
- Laravel Echo
- React Hooks

</td>
</tr>
</table>

### Key Packages

| Package                       | Purpose                 |
|-------------------------------|-------------------------|
| `akira/laravel-followable`    | Following system        |
| `akira/laravel-likeable`      | Like functionality      |
| `akira/laravel-commentable`   | Comments system         |
| `akira/laravel-auth-logs`     | Authentication tracking |
| `spatie/laravel-medialibrary` | Media management        |
| `laravel/socialite`           | OAuth (GitHub & Google) |

> 📖 For more technical details, see the [complete documentation](./docs/README.md)

---

## 🗺️ Roadmap

### ✅ Current Status: Production Ready

#### Core Features (Completed)

- [x] **Authentication & Authorization**
  - [x] User registration & login
  - [x] OAuth integration (GitHub & Google)
  - [x] Email verification
  - [x] Password reset
  - [x] Authentication logs

- [x] **Developer Profiles**
  - [x] Profile creation and customization
  - [x] Avatar and cover images
  - [x] Skills and technologies display
  - [x] Academic background
  - [x] Social links (GitHub, LinkedIn, Twitter, etc.)

- [x] **Hunts (Posts) System**
  - [x] Create, edit, and delete Hunts
  - [x] Media attachments
  - [x] Comments system
  - [x] Like functionality
  - [x] Hunt feed with pagination

- [x] **Social Features**
  - [x] Follow/unfollow system
  - [x] Followers and following lists
  - [x] Activity feeds
  - [x] User discovery
  - [x] Global search

- [x] **Real-time Chat**
  - [x] WebSocket-powered messaging (Laravel Reverb)
  - [x] Private conversations
  - [x] Read receipts
  - [x] Online presence indicators
  - [x] Unread message counts

- [x] **Notifications System**
  - [x] Real-time in-app notifications
  - [x] Email notifications (independent settings)
  - [x] Notification preferences
  - [x] Follow, like, and comment notifications

- [x] **Search & Discovery**
  - [x] Full-text search with Meilisearch
  - [x] User search
  - [x] Hunt search
  - [x] Skill-based discovery
  - [x] Debounced search input

### 🚧 In Progress

- [ ] **Enhanced Gamification** ([Issues #129-188](https://github.com/akira-io/devhunter/issues))
  - [ ] Points & Rewards system
  - [ ] Hunter levels & badges
  - [ ] Leaderboards (weekly, monthly, all-time)
  - [ ] XP for user actions
  - [ ] Badge unlocking and display
  - [ ] Rewards shop with perks
  - [ ] Anti-abuse and fraud detection
  - [ ] Gamification dashboard

- [ ] **Advanced Chat Features** ([Issues #145-152](https://github.com/akira-io/devhunter/issues))
  - [ ] Reply to messages (threading)
  - [ ] Forward messages
  - [ ] Edit sent messages
  - [ ] Delete messages
  - [ ] Message reactions (emoji)
  - [ ] Quote messages
  - [ ] Search messages in conversations
  - [ ] Save/bookmark messages
  - [ ] Read receipts with elapsed time

- [ ] **Privacy & Security** ([Issues #169-177](https://github.com/akira-io/devhunter/issues))
  - [ ] Profile visibility settings
  - [ ] Messaging privacy controls
  - [ ] Hunt comments privacy
  - [ ] Search/Explore visibility
  - [ ] Online status visibility
  - [ ] Blocked users management
  - [ ] Security section (password, 2FA, sessions)
  - [ ] Account deletion

### 🔮 Upcoming Features

- [ ] **Enhanced Profiles**
  - [ ] Project highlights ([Issue #168](https://github.com/akira-io/devhunter/issues/168))
  - [ ] Profile completion indicator ([Issue #24](https://github.com/akira-io/devhunter/issues/24))
  - [ ] Dedicated education table ([Issue #25](https://github.com/akira-io/devhunter/issues/25))
  - [ ] Technologies showcase ([Issue #22](https://github.com/akira-io/devhunter/issues/22))

- [ ] **Onboarding & Tutorials**
  - [ ] Welcome tutorial with replay option ([Issues #178-182](https://github.com/akira-io/devhunter/issues))
  - [ ] Contextual tutorial suggestions
  - [ ] Step-by-step replay
  - [ ] Core user actions tracking

- [ ] **Search Enhancements** ([Issues #156-159](https://github.com/akira-io/devhunter/issues))
  - [ ] Recent searches
  - [ ] Search highlights in results
  - [ ] Search filters and categories
  - [ ] Search analytics

- [ ] **UI/UX Improvements**
  - [ ] Notification settings visual feedback ([Issue #177](https://github.com/akira-io/devhunter/issues/177))
  - [ ] Chat icon hide/show on scroll ([Issue #162](https://github.com/akira-io/devhunter/issues/162))
  - [ ] Notification time window filter ([Issue #164](https://github.com/akira-io/devhunter/issues/164))
  - [ ] Profile icon labels ([Issue #167](https://github.com/akira-io/devhunter/issues/167))


- [ ] **Inertia v2.2 Features**
  - [ ] Infinite scroll implementation ([Issue #136](https://github.com/akira-io/devhunter/issues/136))
  - [ ] Lazy loading ([Issue #137](https://github.com/akira-io/devhunter/issues/137))
  - [ ] Prefetching ([Issue #138](https://github.com/akira-io/devhunter/issues/138))

- [ ] **Code Architecture**
  - [ ] Route attributes migration ([Issue #141](https://github.com/akira-io/devhunter/issues/141))
  - [ ] Performance optimizations
  - [ ] Database query optimization

- [ ] **Community Moderation**
  - [ ] Report Hunts or profiles ([Issue #127](https://github.com/akira-io/devhunter/issues/127))
  - [ ] Block or mute Hunters ([Issue #128](https://github.com/akira-io/devhunter/issues/128))
  - [ ] Content moderation tools

### 🌟 Future Vision

- [ ] **Mobile Applications**
  - [ ] iOS app (Swift)
  - [ ] Android app (Kotlin / Flutter / React Native)
  - [ ] PWA support

- [ ] **Developer Tools**
  - [ ] Job board integration
  - [ ] Project collaboration features
  - [ ] Code snippet sharing
  - [ ] Markdown support for posts

- [ ] **Community Features**
  - [ ] Developer groups/communities
  - [ ] Events & meetups
  - [ ] Mentorship program
  - [ ] Achievement system

- [ ] **Platform Enhancements**
  - [ ] Multiple language support
  - [ ] Developer analytics & insights
  - [ ] Learning resources section
  - [ ] Open source project showcase

> 💬 **Have suggestions?** Check our [GitHub Issues](https://github.com/hunter-cv/web/issues)
> or [join our Discord](https://discord.gg/ghPqZg3RcZ) to share your ideas!

---

## 🤝 Contributing

We welcome contributions from developers of all skill levels! Whether you're fixing a bug, adding a feature, or
improving documentation, your help is appreciated.

### How to Contribute

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/amazing-feature`)
3. **Commit** your changes (`git commit -m 'Add amazing feature'`)
4. **Push** to the branch (`git push origin feature/amazing-feature`)
5. **Open** a Pull Request

### Development Guidelines

- Follow the existing code style
- Write tests for new features
- Update documentation as needed
- Keep commits atomic and well-described
- Run tests before submitting PR: `php artisan test`

### Code of Conduct

Please read our [Code of Conduct](CODE_OF_CONDUCT.md) to understand what behavior we expect from all participants in the
Dev Hunter community.

> 📖 For detailed contributing guidelines, see [CONTRIBUTING.md](CONTRIBUTING.md)

---

## 📬 Community

Join our vibrant community of developers!

### Get Involved

<table>
<tr>
<td align="center" width="33%">

### 💬 Discord

[Join our server](https://discord.gg/ghPqZg3RcZ)

Chat with the team and community

</td>
<td align="center" width="33%">

### 🐛 GitHub Issues

[Report bugs](https://github.com/hunter-cv/web/issues)

Found a bug? Let us know!

</td>
<td align="center" width="33%">

### ✉️ Email

[geral@akira-io.com](mailto:kidiatoliny@gmail.com)

General inquiries

</td>
</tr>
</table>

### Support the Project

- ⭐ **Star** this repository
- 🐛 **Report** bugs and issues
- 💡 **Suggest** new features
- 📖 **Improve** documentation
- 🔀 **Submit** pull requests
- 💬 **Share** Hunter with others

---

## 📄 License

This project is licensed under the **[GNU Affero General Public License v3.0](LICENSE.md)**.

This means:

- ✅ You can use, modify, and distribute this software
- ✅ Source code must be made available
- ✅ Changes must be documented
- ✅ Network use is considered distribution

---

<div align="center">

### Built with ❤️ by the Cape Verdean Developer Community

**[Website](https://hunter.cv)** • **[Documentation](./docs)** • **[Discord](https://discord.gg/ghPqZg3RcZ)** • *
*[GitHub](https://github.com/hunter-cv/web)**

⭐ Star us on GitHub — it helps!

</div>
