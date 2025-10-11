# Changelog

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 0.6.0 (2025-10-11)

### Features

#### Progressive Web App (PWA)

- **Installable Application**: Full PWA support with manifest and service worker
- **Offline Support**: Service worker caching for offline functionality
- **Automatic Updates**: Cache versioning and automatic update system
- **iOS Optimization**: Safe area support and mobile-optimized layouts
- **App-like Experience**: Native-like feel on all platforms

#### Real-time Chat Enhancements

- **Mobile Optimization**: iOS keyboard handling with fixed header and input
- **Improved UX**: Fixed layout preventing scroll issues on mobile
- **WebSocket Authorization**: Enhanced channel authorization with proper scope handling
- **Real-time Presence**: Online/offline status indicators

#### Security & Sessions

- **Active Sessions Management**: Users can now view and manage all active login sessions across devices
- **Session Filtering**: Automatically displays only the most recent session per IP address to reduce clutter
- **Custom React Hook**: Added `useUniqueSessions` hook for optimized session filtering with memoization
- **Session Details**: Shows device type, browser, OS, location, and last active time for each session
- **Logout Controls**: Users can revoke individual sessions or logout from all devices at once

#### Social Features

- **Follow Button Fix**: Fixed follow/unfollow button state in Huntings page dropdown menu
- **Block User System**: Complete implementation of user blocking functionality
- **OAuth Management**: Enhanced OAuth account disconnection with proper validation and logging

#### UI/UX Improvements

- **TypeScript Integration**: Full type safety with TypeScript across session management components
- **Real-time Updates**: Sessions update dynamically without page reload
- **Visual Feedback**: Clear status indicators for current session vs. other devices

- **Radix UI Integration**: Complete component library with Tailwind CSS v4
- **Modern Components**: Alert dialogs, popovers, dropdowns, and more
- **Dark Mode**: Full dark mode support across all components
- **Responsive Design**: Mobile-first approach with tablet and desktop optimization
- **Typography**: Improved font hierarchy and readability

### Tests & Quality

#### Massive Test Coverage Improvements

- **101 Tests** passing with **284 assertions** (up from 62 tests)
- **5 Classes** achieved **100% test coverage**:
  - BlockedUser Model
  - BlockUserAction
  - ActiveSessionsAction
  - HuntMetrics DTO
  - MetricsContext DTO

#### New Test Suites

- Added **BlockedUserTest** (5 tests) - 100% coverage
- Added **BlockUserActionTest** (6 tests) - 100% coverage
- Added **ActiveSessionsActionTest** (5 tests) - 100% coverage
- Added **ProcessHuntImageActionTest** (7 tests) - ~90% coverage
- Added **SyncRecentHuntViewsActionTest** (6 tests) - ~98% coverage
- Added **HuntMetricsTest** (11 tests) - 100% coverage
- Added **MetricsContextTest** (10 tests) - 100% coverage
- Enhanced **UserTest** (+6 tests) - ~95% coverage
- Enhanced **HuntTest** (+3 tests) - ~88% coverage

#### Coverage Improvements by Class

- **BlockedUser**: 50% → 100% (+50%)
- **ProcessHuntImageAction**: 63.2% → 90% (+27%)
- **BlockUserAction**: 75% → 100% (+25%)
- **SyncRecentHuntViewsAction**: 77.8% → 98% (+20%)
- **Hunt Model**: 68% → 88% (+20%)
- **User Model**: 89.5% → 95% (+5.5%)
- **Overall Average**: ~75% → **~95%**

### Technical Improvements

#### Backend

- **Immutable DTOs**: MetricsContext now properly implements immutable pattern with `withCalculated()`
- **Action Classes**: Multiple action classes marked as `readonly` for better immutability
- **Database Optimization**: Active sessions query optimized to return only latest per IP
- **Error Handling**: Enhanced exception handling with proper logging in ProcessHuntImageAction
- **Laravel 12.32.5**: Updated to latest Laravel framework
- **PHP 8.4.13**: Latest PHP version with performance improvements
- **Type Safety**: Enhanced type hints and PHPDoc annotations
- **Route Scopes**: Fixed scope usage in broadcast channels
- **Database Optimization**: Query improvements and eager loading

#### Frontend

- **Custom Hooks**: Created reusable `useUniqueSessions` hook with performance optimization
- **Type Safety**: Full TypeScript coverage for session management
- **Component Architecture**: Better separation of concerns in session-related components
- **Memoization**: Used React.useMemo for expensive filtering operations
- **React 19.1**: Upgraded to latest React version
- **TypeScript**: Full type coverage across components
- **Vite 7.1**: Latest build tool with improved performance
- **Tailwind CSS 4.1**: Updated styling framework
- **Component Architecture**: Better separation of concerns and reusability

### Bug Fixes

- Fixed follow button not updating state in Huntings page dropdown menu
- Fixed duplicate session display for same IP addresses
- Fixed session current state not respecting `hasFollowed` prop in Onboarding component
- Resolved issues with `user.has_followed` property in dropdown menus
- Fixed iOS keyboard pushing chat header off-screen
- Fixed scroll behavior in chat when keyboard is open
- Fixed `forUser()` scope usage in broadcast channel authorization
- Improved mobile touch interactions and responsiveness
- Fixed safe area padding on iOS devices

### Documentation

- Added comprehensive JSDoc documentation for `useUniqueSessions` hook
- Improved inline code documentation across test files
- Updated test descriptions for better clarity
- Updated README with current tech stack versions
- Added PWA features to documentation
- Enhanced chat system documentation
- Updated package version information
- Improved installation prerequisites

### Infrastructure

- Test suite execution time: **9.03s** for 101 tests
- Zero failing tests, 100% success rate
- CI/CD pipeline remains stable with enhanced test coverage

### Key Metrics

```
✅ Tests: 101 (up from 62)
✅ Assertions: 284 (up from 147)  
✅ Test Files: 10 new files created
✅ Classes with 100% Coverage: 5
✅ Classes with >95% Coverage: 4
✅ Average Coverage: ~95%
```

### Dependencies

#### Backend

- `laravel/framework`: ^12.32.5
- `laravel/reverb`: ^1.6
- `laravel/sanctum`: ^4.2
- `laravel/scout`: ^10.19
- `laravel/socialite`: ^5.23
- `laravel/horizon`: ^5.34
- `laravel/pulse`: ^1.4
- `laravel/nightwatch`: ^1.14

#### Frontend

- `react`: ^19.1.1
- `@inertiajs/react`: ^2.2.4
- `tailwindcss`: ^4.1.13
- `vite`: ^7.1.9
- `@radix-ui/*`: Updated to latest versions
- `laravel-echo`: ^2.2.4

## 0.5.0 (2025-05-27)

### Features

- Added Google authentication support
- Included `email_verified_at` field in user data from GoogleAuthController
- Implemented authentication logs

### Fixes

- Prevented multiple submissions during comment creation
- Renamed `avatar` field to `avatar_url` in GoogleAuthController
- Fixed potential DOM XSS issue: text incorrectly reinterpreted as HTML

### Refactors & Improvements

- Updated GoogleAuthController: now uses `readonly` class and improved password hashing

## 0.4.0 (2025-05-20)

### Features

- Added image upload functionality for hunts, enabling richer content sharing
- Introduced `ProfileAvatarCard` component for user profile image management
- Enhanced `GetHuntersAction` to include additional user profile data

### Tests & Coverage

- Added unit tests for avatar retrieval (`GetAvatarAction`)
- Added tests for user rate limiting and email verification notifications
- Improved GitHub authentication test coverage
- Updated throttle error messages to reflect correct wait times
- Increased test coverage thresholds to 100%

### Refactors & Improvements

- Updated profile image handling with consistent background image naming
- Improved user resource structure and repository links
- Required verified users for skill and comment actions
- Marked action classes as `readonly` and improved validation in `ProfileUpdateRequest`
- Removed `coverage/` folder for cleanup

## 0.3.0 (2025-05-13)

### UI and UX Improvements

- Enhanced `ProfileCompletion` component with visual feedback and responsive layout
- Updated `Welcome` component styles, added GitHub and Discord links, and improved placeholder text
- Improved layout responsiveness across `Links`, `Sidebar`, and other components
- Refined component styles with better spacing, gradients, and accessibility enhancements

### Refactors and Naming Consistency

- Renamed all mentions of "Dev Hunter" to "Hunter" throughout the UI
- Rebranded "Feed" as "Hunt Line", including updated routes and components
- Replaced the term "Followers" with "Hunters" for consistent branding
- Simplified sidebar state handling and reorganized related logic
- Cleaned up class names and removed redundant styles for better maintainability

### Fixes and General Improvements

- Improved avatar rendering responsiveness
- Adjusted padding and spacing across various components
- Enhanced test coverage configuration and updated internal documentation

## 0.2.0 (2025-05-06)

### Features

- Introduced the Finder component with user search functionality
- Added hunts: users can now create, like, and comment on hunts
- Implemented comment support with like and delete actions
- Added command dialog for faster search interactions
- Integrated Pan analytics to improve insights and metrics

### UI and UX Improvements

- Enhanced the ProfileCompletion component with visual feedback and responsive layout
- Updated the Welcome component with new styles, GitHub and Discord links, and improved placeholder text
- Improved responsiveness for Links, Sidebar, and other layout elements
- Refined styles across components with better spacing, gradients, and accessibility features

### Refactors and Naming Consistency

- Renamed "Dev Hunter" to "Hunter" throughout the interface
- Rebranded "Feed" to "Hunt Line" and updated routes and components accordingly
- Replaced "Followers" with "Hunters" for a more consistent brand language
- Simplified sidebar state handling and reorganized related logic
- Cleaned up class names and removed redundant styling for better maintainability

### Fixes and General Improvements

- Improved avatar rendering for better responsiveness
- Adjusted padding and spacing in multiple components
- Improved test coverage configuration and internal documentation

## 0.1.1 (2025-04-29)

### Added

- Docker setup for DevHunter via Laravel Sail. ([#50](https://github.com/akira-io/devhunter/pull/50))

### Fixed

- Comment formatting in `WelcomeController.php` improved for clarity. (`27a5c65`)
- Handled empty `users` array in Welcome component and ensured proper use of paginator data. (`ba837e4`)

### Changed

- Updated environment configuration and README to provide clearer setup instructions. (`67708dc`)

## 0.1.0 (2025-04-29)

### Added

- User follow/unfollow system.
- GitHub authentication and extended user profiles.
- `CreateHunt` and `FloatingCreateHunt` components.
- Onboarding flow with follow suggestions.
- Admin panel for platform management.
- Meilisearch integration via Laravel Scout.
- Profile editing for professional education and skills.
- Redesigned profile page with highlights, bio, social links, and follower count.
- `DevCount`, `NavUser`, and `Highlights` components.
- Cybersecurity skills in tech stack.
- Search debouncing.
- Images in README.
- Code of Conduct and Security Policy documents.

### Changed

- Enhanced frontend layout and mobile responsiveness.
- Improved onboarding suggestions with better randomization and structure.
- Added loading indicators to search input.
- Refined route names and search timeouts.
- Updated README and repository links.

### Fixed

- General UI inconsistencies and layout issues.

### Security

- Set up `laravel-debugbar` as a dev-only tool.
- Added Security Policy documentation.

### Infrastructure

- GitHub Actions: formatting, linting, Meilisearch setup.
- CI/CD: steps for PHP, Node.js, and wait-for-Meilisearch logic.
- `release-it` setup with custom changelog grouping.
- Dependabot integration for dependency updates.

### Maintenance

- Architectural tests and strict typing in controllers.
- Cleanup of `package.json` and configuration files.
- Updated changelog tooling and test coverage thresholds.
- Consistent formatting and typing across frontend using Prettier and TypeScript settings.
