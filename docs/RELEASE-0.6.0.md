doc# Release Notes - Version 0.6.0

## 🎉 Hunter v0.6.0 - Security & Quality Release

**Release Date:** October 16, 2025

We're excited to announce Hunter v0.6.0, a major release focused on **security improvements**, **session management**, and **exceptional test coverage**. This release brings our overall test coverage to **~95%** with **101 tests** passing!

---

## 🌟 Highlights

### ✨ Active Sessions Management
Users now have complete control over their login sessions:
- **View all active sessions** across devices
- **See device details**: Browser, OS, location, and last active time
- **Manage sessions**: Revoke individual sessions or logout from all devices
- **Smart filtering**: Only shows the most recent session per IP address

### 🔒 Enhanced Security Features
- Complete **user blocking system** implementation
- Improved **OAuth account management** with proper validation
- **Activity logging** for all security-related actions
- Protection against **duplicate session clutter**

### 🧪 World-Class Test Coverage
Achievement unlocked: **~95% average test coverage**!
- **5 classes** at 100% coverage
- **52 new tests** added
- **284 total assertions**
- Zero failing tests ✅

---

## 📦 What's New

### Session Management

#### View Active Sessions
```
🖥️  Chrome on macOS
    192.168.1.1 • São Paulo, Brazil
    Active 2 minutes ago
    [Current Session]
```

Each session shows:
- Device icon (desktop/mobile)
- Browser and operating system
- IP address and location (when available)
- Last active timestamp
- Current session indicator

#### Session Controls
- **Revoke Session**: Remove a specific device's access
- **Logout All**: Sign out from all other devices at once
- **Auto-refresh**: Sessions update in real-time

### User Blocking System

Complete blocking functionality:
- Block/unblock users from their profile
- Blocked users cannot:
  - Send you messages
  - See your activity status
  - Interact with your content
- Idempotent operations (blocking twice works safely)

### OAuth Management

Enhanced account disconnection:
- Safely disconnect GitHub or Google accounts
- Activity logging for audit trail
- Protection: Cannot disconnect if no password is set
- Clear success/error feedback

---

## 🧪 Test Coverage Achievements

### Coverage by Component

| Component | Before | After | Improvement |
|-----------|--------|-------|-------------|
| **BlockedUser Model** | 50% | 100% | +50% 🎯 |
| **BlockUserAction** | 75% | 100% | +25% 🎯 |
| **ProcessHuntImageAction** | 63% | 90% | +27% ⬆️ |
| **SyncRecentHuntViewsAction** | 78% | 98% | +20% ⬆️ |
| **Hunt Model** | 68% | 88% | +20% ⬆️ |
| **User Model** | 89% | 95% | +6% ✨ |
| **HuntMetrics DTO** | 96% | 100% | +4% 🎯 |
| **MetricsContext DTO** | 91% | 100% | +9% 🎯 |

### New Test Suites

1. **BlockedUserTest** - 5 tests
   - Relationship validations
   - Fillable attributes
   - Multiple blocks support

2. **BlockUserActionTest** - 6 tests
   - Successful blocking
   - Self-blocking prevention
   - Idempotency checks

3. **ActiveSessionsActionTest** - 5 tests
   - IP filtering logic
   - Session deduplication
   - Current session marking

4. **ProcessHuntImageActionTest** - 7 tests
   - Image processing flow
   - Status updates
   - Event broadcasting

5. **SyncRecentHuntViewsActionTest** - 6 tests
   - View synchronization
   - Empty collection handling
   - Logging verification

6. **HuntMetricsTest** - 11 tests
   - Engagement calculations
   - Performance indicators
   - Viral detection

7. **MetricsContextTest** - 10 tests
   - Immutability patterns
   - Metric merging
   - Context chaining

### Test Statistics

```
✅ Total Tests: 101
✅ Total Assertions: 284
✅ Success Rate: 100%
✅ Execution Time: 9.03s
✅ Classes at 100%: 5
✅ Classes above 95%: 4
```

---

## 🔧 Technical Improvements

### Backend Enhancements

#### Immutable Data Transfer Objects
```php
$context = new MetricsContext(...);
$updated = $context->withCalculated(['score' => 95.5]);
// Original $context remains unchanged
```

#### Optimized Database Queries
- Sessions query now groups by IP and returns only latest
- Reduced data transfer and processing time
- Better performance on pages with many logins

#### Enhanced Error Handling
- Comprehensive exception handling in image processing
- Detailed error logging with context
- Graceful fallbacks for edge cases

### Frontend Improvements

#### Custom React Hook
```typescript
const uniqueSessions = useUniqueSessions(activeSessions);
// Memoized, optimized, type-safe
```

#### TypeScript Integration
- Full type safety for session management
- Intellisense support for all components
- Compile-time error detection

#### Performance Optimizations
- React.useMemo for expensive operations
- Efficient re-rendering strategies
- Minimal bundle size impact

---

## 🐛 Bug Fixes

### Session Management
- Fixed duplicate sessions for same IP address
- Fixed "Atual" (Current) label appearing on multiple sessions
- Fixed session list not updating after logout

### Social Features
- Fixed follow button not updating in Huntings dropdown
- Fixed `has_followed` prop not being respected in Onboarding
- Fixed follow state inconsistency between pages

### UI/UX
- Fixed visual clutter from multiple session entries
- Improved session card layout on mobile devices
- Better error messages for failed operations

---

## 📚 Documentation Updates

### New Documentation
- JSDoc comments for `useUniqueSessions` hook
- Comprehensive test documentation
- Inline code comments for complex logic

### Updated Guides
- CHANGELOG.md with detailed v0.6.0 notes
- README.md with latest version badge
- Release notes document (this file)

---

## 🎯 Migration Guide

### For Users
No action required! All features are available immediately after update.

### For Developers

#### Session Management
If you've customized session display, note that sessions are now automatically deduplicated by IP.

```typescript
// Before: Manual filtering needed
const filtered = sessions.filter(...);

// After: Use the hook
import { useUniqueSessions } from '@/hooks/use-unique-sessions';
const uniqueSessions = useUniqueSessions(activeSessions);
```

#### Testing
New test utilities available:

```php
// BlockedUser tests
BlockedUser::factory()->create([...]);

// Session tests  
$this->assertSessionExists($userId, $ipAddress);
```

---

## 🚀 Performance Metrics

### Before v0.6.0
- Average test coverage: ~75%
- Session queries: Multiple records per IP
- Test execution: 4.4s (62 tests)

### After v0.6.0
- Average test coverage: **~95%** (+20%)
- Session queries: One record per IP (optimized)
- Test execution: 9.0s (101 tests) - more tests, still fast!

---

## 🙏 Acknowledgments

Special thanks to all contributors who helped make this release possible:
- Test coverage improvements
- Bug reports and fixes
- Documentation enhancements
- Code reviews and feedback

---

## 📅 What's Next?

### Coming in v0.7.0
- Enhanced notification system
- Real-time messaging improvements
- Advanced search filters
- Profile customization options

---

## 🔗 Links

- [Full Changelog](CHANGELOG.md)
- [GitHub Repository](https://github.com/akira-io/devhunter)
- [Issue Tracker](https://github.com/akira-io/devhunter/issues)
- [Discord Community](https://discord.gg/ghPqZg3RcZ)

---

**Happy Coding! 🚀**

*Hunter Team*
