
# Hunter Documentation

Welcome to the Hunter documentation! This comprehensive guide covers all aspects of the platform, from getting started to advanced features.
1. **[Getting Started](./01-getting-started.md)**
   - Installation & setup
   - Environment configuration
   - Database setup (PostgreSQL)
   - OAuth configuration
   - Development tools

2. **[Authentication](./02-authentication.md)**
   - Registration & login
   - OAuth (GitHub & Google)
   - Password reset
   - Email verification
   - API authentication with Sanctum


3. **[User Profiles](./03-user-profiles.md)**
   - Profile management
   - Avatar & background images
   - Skills management
   - Academic background
   - Social links
   - Profile search

4. **[Hunts (Posts)](./04-hunts.md)**
   - Creating hunts
   - Media attachments
   - Likes & comments
   - Pinning hunts
   - Hunt search
   - Hunt feed

5. **[Social Features](./05-social-features.md)**
   - Following system
   - Likes (hunts & comments)
   - Comments
   - Notifications
   - User discovery
   - Activity feeds

6. **[Real-time Chat](./06-real-time-chat.md)**
   - WebSocket configuration
   - Conversations
   - Messages
   - Read receipts
   - User presence
   - Broadcasting events

7. **[Search & Discovery](./07-search-discovery.md)**
   - Meilisearch setup
   - User search
   - Hunt search
   - Skill-based search
   - Search suggestions
   - Analytics


8. **[API Reference](./08-api-reference.md)**
   - Authentication
   - Endpoints
   - Request/response formats
   - Rate limiting
   - WebSocket events
   - SDK examples

9. **[Frontend Development](./09-frontend-development.md)**
   - React & TypeScript
   - Inertia.js integration
   - Custom hooks
   - Real-time features
   - Tailwind CSS
   - Component examples

10. **[Testing](./10-testing.md)**
    - Pest PHP
    - Feature tests
    - Unit tests
    - API testing
    - Browser testing
    - CI/CD



11. **[Notifications System](./11-notifications-system.md)**
    - Real-time notifications
    - Email notifications
    - Notification preferences
    - In-app vs email independence
    - Notification API
    - Testing notifications

12. **[Gamification System](./12-gamification-system.md)**
    - XP & Level system
    - Badge system
    - Rewards & perks
    - Leaderboards
    - Anti-abuse detection
    - Progress tracking

13. **[Advanced Chat Features](./13-advanced-chat-features.md)**
    - Reply to messages (Threading)
    - Forward messages
    - Edit & delete messages
    - Message reactions (Emoji)
    - Quote messages
    - Search in conversations
    - Save/bookmark messages
    - Read receipts with timestamps

14. **[Privacy & Security](./14-privacy-security.md)**
    - Profile visibility controls
    - Messaging privacy
    - Hunt comments privacy
    - Search/Explore visibility
    - Online status visibility
    - Blocked users management
    - Password management
    - Two-factor authentication (2FA)
    - Active sessions management
    - Connected accounts (OAuth)
    - Account deletion

15. **[Deployment & Production](./15-deployment-production.md)**
    - Production server setup
    - PostgreSQL configuration
    - Nginx configuration
    - SSL/TLS with Let's Encrypt
    - Supervisor for queues & WebSockets
    - Performance optimization
    - Monitoring & logging
    - Backup strategies
    - Security checklist

16. **[Markdown Editor](./16-markdown-editor.md)**
    - Complete markdown editing system
    - Emoji picker with categories
    - Syntax highlighting
    - Real-time preview
    - Markdown help guide
    - Auto-detection of code
    - Responsive design
    - Dark mode support
    - Accessibility features

17. **[Chat System Architecture](./17-chat-system-architecture.md)**
    - Dedicated chat layout
    - Type-safe routes with Wayfinder
    - ChatContext provider
    - Presence system
    - Conversation management
    - Real-time events
    - Unread messages badge
    - Performance optimization
    - Security & authorization
    - Mobile responsiveness

18. **[Hunt Creation UX](./18-hunt-creation-ux.md)**
    - Modern UI redesign
    - Character counter with progress bar
    - Real-time validation feedback
    - Success animations
    - Image upload with preview
    - Focus states and micro-interactions
    - Toast notifications
    - Accessibility features
    - Responsive design
    - Integration examples

19. **[Active Sessions Management](./19-active-sessions.md)**
    - Session detection and tracking
    - Smart IP filtering
    - Device and browser information
    - Session revocation controls
    - Logout all devices feature
    - Security best practices
    - TypeScript implementation
    - Custom React hooks
    - Complete testing guide

20. **[User Blocking System](./20-user-blocking.md)**
    - Block/unblock users
    - Interaction restrictions
    - Privacy controls
    - Database schema
    - Backend implementation
    - Frontend components
    - Comprehensive testing
    - Usage examples
    - Security considerations

21. **[Test Coverage Improvements](./21-test-coverage.md)**
    - 95% average coverage achieved
    - 101 tests with 284 assertions
    - Test suite improvements
    - Coverage by component
    - Testing strategies
    - Best practices
    - CI/CD integration
    - Performance metrics

22. **[Hunt Metrics System](./22-hunt-metrics-system.md)**
    - Comprehensive analytics for hunts
    - Pipeline architecture pattern
    - Engagement, reach, and quality metrics
    - Weighted scoring system
    - Virality coefficient calculation
    - Performance levels and rankings
    - DTOs and type safety
    - 100% test coverage
    - Usage examples and API

23. **[Unsaved Changes Guard](./23-unsaved-changes-guard.md)**
- Prevent accidental data loss
- Automatic change detection
- Inertia & browser navigation handling
- Accessible confirmation dialogs
- Mobile-optimized layouts
- Custom hooks and components
- TypeScript support
- Full test coverage
- Integration examples

## 📖 Quick Reference

| Document | Description |
|----------|-------------|
| [**Quick Reference**](./QUICK-REFERENCE.md) | Common commands and quick examples |
| [**Installation**](./01-getting-started.md) | Start here for local development |
| [**API Docs**](./08-api-reference.md) | Complete API documentation |
| [**Frontend Guide**](./09-frontend-development.md) | React, TypeScript, Inertia |
| [**Testing Guide**](./10-testing.md) | Pest PHP testing |
| [**Production Deploy**](./15-deployment-production.md) | Deploy to production with PostgreSQL |

## 🌟 Quick Links by Topic

### For Developers
- **New to the project?** → [Getting Started](./01-getting-started.md)
- **Building features?** → [Frontend Development](./09-frontend-development.md)
- **Writing tests?** → [Testing Guide](./10-testing.md)
- **Deploying?** → [Deployment & Production](./15-deployment-production.md)

### For Users
- **Authentication** → [Authentication Guide](./02-authentication.md)
- **Privacy Settings** → [Privacy & Security](./14-privacy-security.md)
- **Gamification** → [Gamification System](./12-gamification-system.md)
- **Chat Features** → [Advanced Chat Features](./13-advanced-chat-features.md)

### Key Platform Features
- ✅ **Social Network**: Follow developers, like posts, comment on hunts
- ✅ **Real-time Chat**: WebSocket-powered instant messaging with advanced features
- ✅ **Search**: Powerful full-text search with Meilisearch
- ✅ **Profiles**: Comprehensive developer portfolios
- ✅ **OAuth**: GitHub and Google authentication
- ✅ **Gamification**: XP, Levels, Badges, and Rewards
- ✅ **Notifications**: Real-time and email notifications with granular controls
- ✅ **Privacy**: Comprehensive privacy and security settings

## Technology Stack

### Backend
- **Laravel 12** - PHP framework
- **PostgreSQL** - Primary database
- **Redis** - Cache & queues
- **Meilisearch** - Search engine
- **Laravel Reverb** - WebSocket server
- **Laravel Scout** - Search integration
- **Laravel Sanctum** - API authentication

### Frontend
- **React 18** - UI library
- **TypeScript** - Type safety
- **Inertia.js v2** - Modern monolith
- **Tailwind CSS v4** - Utility-first CSS
- **Vite** - Build tool
- **Laravel Echo** - WebSocket client

### Packages
- **akira/laravel-followable** - Following system
- **akira/laravel-likeable** - Like functionality
- **akira/laravel-commentable** - Comments system
- **spatie/laravel-medialibrary** - Media management
- **laravel/socialite** - OAuth providers

## Contributing

Please read our [Contributing Guide](../CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

## Community

- **Discord**: [Join our community](https://discord.gg/ghPqZg3RcZ)
- **GitHub Issues**: [Report bugs](https://github.com/akira-io/devhunter/issues)
- **Email**: geral@akira-io.com

## Support

If you need help:

1. **Check the documentation** - Most questions are answered in our comprehensive docs
2. **Quick Reference** - Check [QUICK-REFERENCE.md](./QUICK-REFERENCE.md) for common commands
3. **Search Issues** - Look through existing [GitHub Issues](https://github.com/akira-io/devhunter/issues)
4. **Ask on Discord** - Join our [Discord server](https://discord.gg/ghPqZg3RcZ) for community help
5. **Create an Issue** - If you found a bug or have a feature request

## What's New in the Documentation

### Version 0.6.0 (Latest - October 16, 2025)

- 🔒 **[Active Sessions Management](RELEASE-0.6.0.md)** - Complete guide to viewing and managing login sessions
- ✅ **[Test Coverage Report](RELEASE-0.6.0.md#-test-coverage-achievements)** - 101 tests with 95% average coverage
- 🛡️ **[Enhanced Security](RELEASE-0.6.0.md#-enhanced-security-features)** - User blocking and OAuth improvements
- 📋 **[Migration Guide](MIGRATION-0.6.0.md)** - Upgrade from v0.5.0 to v0.6.0
- ⚡ **[Performance Improvements](RELEASE-0.6.0.md#-performance-metrics)** - Optimized session queries

### Previous Versions

- ✨ **Markdown Editor** - Comprehensive guide to the markdown editing system with emojis, preview, and help
- ✨ **Notifications System** - Complete guide to real-time and email notifications
- 🎮 **Gamification** - XP, Levels, Badges, Rewards, and Leaderboards
- 💬 **Advanced Chat** - Reply, Forward, Edit, Delete, Reactions, and more
- 🔒 **Privacy & Security** - Comprehensive privacy controls and security settings
- 🚀 **Production Deployment** - Full guide for deploying with PostgreSQL

## License

Hunter is open-source software licensed under the [GNU Affero General Public License](../LICENSE.md).

## Acknowledgments

**Built with ❤️ by the Akira team and the Cape Verdean developer community.**

---

> **Version Information**: Current version **v0.6.0** (October 16, 2025). See [RELEASE-0.6.0.md](RELEASE-0.6.0.md) for
> release notes, [MIGRATION-0.6.0.md](MIGRATION-0.6.0.md) for migration guide, and [CHANGELOG.md](../CHANGELOG.md) for
> complete version history.

