# 🎮 Gamification System

## Overview

Hunter includes a comprehensive gamification system to motivate and reward user engagement. The system features XP, Levels, Badges, Rewards, Leaderboards, and anti-abuse mechanisms.

## Architecture

### Components

```
┌─────────────────────────────────────────────────┐
│          Gamification Dashboard                  │
│  (Settings → Gamification)                      │
└─────────────────────────────────────────────────┘
                    │
        ┌───────────┼───────────┐
        │           │           │
    ┌───▼───┐   ┌──▼──┐   ┌────▼────┐
    │  XP   │   │Badge│   │ Rewards │
    │ Level │   │     │   │  Perks  │
    └───┬───┘   └──┬──┘   └────┬────┘
        │          │           │
        └──────────┴───────────┘
                   │
          ┌────────▼─────────┐
          │   Leaderboards   │
          └──────────────────┘
```

## 1. XP & Level System

### How It Works

Users earn **Experience Points (XP)** by performing actions on the platform. As they accumulate XP, they **level up**, unlocking rewards and recognition.

### XP Sources

| Action                    | XP Earned | Notes                          |
|---------------------------|-----------|--------------------------------|
| Create a Hunt             | 10        | Per Hunt                       |
| Receive a comment         | 5         | Per comment                    |
| Receive a like            | 2         | Per like                       |
| Follow another Hunter     | 3         | Per follow                     |
| Send a message            | 1         | Per message (throttled)        |
| Use Search/Explore        | 1         | First time daily               |
| Complete Profile          | 50        | One-time bonus                 |
| Unlock a Badge            | 20-100    | Depends on badge tier          |

### Level Thresholds

```php
// Example level progression
[
    1 => 0,       // Level 1: 0 XP
    2 => 100,     // Level 2: 100 XP
    3 => 250,     // Level 3: 250 XP
    4 => 500,     // Level 4: 500 XP
    5 => 1000,    // Level 5: 1000 XP
    6 => 2000,    // Level 6: 2000 XP
    7 => 3500,    // Level 7: 3500 XP
    8 => 5500,    // Level 8: 5500 XP
    9 => 8000,    // Level 9: 8000 XP
    10 => 12000,  // Level 10: 12000 XP
    // ... up to Level 50+
]
```

### Database Schema

```sql
-- Add to users table
ALTER TABLE users ADD COLUMN xp INTEGER DEFAULT 0;
ALTER TABLE users ADD COLUMN level INTEGER DEFAULT 1;

-- XP Events Log
CREATE TABLE xp_events (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    points INTEGER NOT NULL,
    metadata JSONB,
    created_at TIMESTAMP DEFAULT NOW(),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_created (user_id, created_at)
);

-- XP Decisions (for anti-abuse)
CREATE TABLE xp_decisions (
    id BIGSERIAL PRIMARY KEY,
    event_id BIGINT NOT NULL,
    decision VARCHAR(20) NOT NULL, -- 'granted', 'throttled', 'denied'
    reason_code VARCHAR(50),
    applied_points INTEGER NOT NULL,
    throttle_until TIMESTAMP,
    
    FOREIGN KEY (event_id) REFERENCES xp_events(id) ON DELETE CASCADE
);
```

### Implementation

#### Award XP

```php
namespace App\Services;

class XpService
{
    public function awardXp(User $user, string $eventType, int $points, array $metadata = []): void
    {
        // Create event
        $event = XpEvent::create([
            'user_id' => $user->id,
            'event_type' => $eventType,
            'points' => $points,
            'metadata' => $metadata,
        ]);
        
        // Apply anti-abuse checks
        $decision = $this->validateXpGrant($user, $eventType, $points);
        
        if ($decision['granted']) {
            $user->increment('xp', $decision['points']);
            $this->checkLevelUp($user);
        }
        
        // Log decision
        XpDecision::create([
            'event_id' => $event->id,
            'decision' => $decision['status'],
            'reason_code' => $decision['reason'] ?? null,
            'applied_points' => $decision['points'],
            'throttle_until' => $decision['throttle_until'] ?? null,
        ]);
    }
    
    protected function checkLevelUp(User $user): void
    {
        $newLevel = $this->calculateLevel($user->xp);
        
        if ($newLevel > $user->level) {
            $user->update(['level' => $newLevel]);
            event(new UserLeveledUp($user, $newLevel));
        }
    }
    
    protected function calculateLevel(int $xp): int
    {
        $thresholds = config('gamification.level_thresholds');
        
        foreach (array_reverse($thresholds, true) as $level => $requiredXp) {
            if ($xp >= $requiredXp) {
                return $level;
            }
        }
        
        return 1;
    }
}
```

#### Usage Example

```php
use App\Services\XpService;

// When a Hunt is created
$xpService = app(XpService::class);
$xpService->awardXp($user, 'hunt_created', 10, [
    'hunt_id' => $hunt->id,
]);
```

### Frontend Display

```tsx
interface XpBarProps {
    currentXp: number;
    currentLevel: number;
    nextLevelXp: number;
}

export function XpBar({ currentXp, currentLevel, nextLevelXp }: XpBarProps) {
    const progress = (currentXp / nextLevelXp) * 100;
    
    return (
        <div className="space-y-2">
            <div className="flex justify-between text-sm">
                <span className="font-semibold">Level {currentLevel}</span>
                <span className="text-muted-foreground">
                    {currentXp} / {nextLevelXp} XP
                </span>
            </div>
            <div className="h-2 bg-secondary rounded-full overflow-hidden">
                <div 
                    className="h-full bg-primary transition-all duration-500"
                    style={{ width: `${progress}%` }}
                />
            </div>
        </div>
    );
}
```

## 2. Badge System

### Badge Types

#### Achievement Badges

| Badge             | Criteria                          | XP Reward |
|-------------------|-----------------------------------|-----------|
| **Onboarded**     | Complete all onboarding steps     | 50        |
| **First Hunt**    | Create your first Hunt            | 20        |
| **First Comment** | Comment on a Hunt                 | 10        |
| **Explorer**      | Use search/explore                | 10        |
| **Connector**     | Follow 5+ Hunters                 | 30        |
| **Communicator**  | Send 10 messages                  | 20        |
| **Popular**       | Receive 50 likes                  | 50        |
| **Influencer**    | Get 100 followers                 | 100       |

#### Tier Badges

- **Bronze** (1-99 XP bonus)
- **Silver** (100-499 XP bonus)
- **Gold** (500+ XP bonus)

### Database Schema

```sql
-- Badges Definition
CREATE TABLE badges (
    id BIGSERIAL PRIMARY KEY,
    key VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    tier VARCHAR(20), -- 'bronze', 'silver', 'gold'
    icon_url VARCHAR(255),
    xp_reward INTEGER DEFAULT 0,
    criteria JSONB,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- User Badges (Pivot)
CREATE TABLE user_badges (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    badge_id BIGINT NOT NULL,
    unlocked_at TIMESTAMP DEFAULT NOW(),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
    UNIQUE (user_id, badge_id)
);
```

### Implementation

```php
namespace App\Services;

class BadgeService
{
    public function checkAndAwardBadges(User $user): void
    {
        $badges = Badge::all();
        
        foreach ($badges as $badge) {
            if ($this->meetsRequirements($user, $badge) && !$user->hasBadge($badge)) {
                $this->awardBadge($user, $badge);
            }
        }
    }
    
    protected function awardBadge(User $user, Badge $badge): void
    {
        $user->badges()->attach($badge->id, [
            'unlocked_at' => now(),
        ]);
        
        // Award XP
        if ($badge->xp_reward > 0) {
            app(XpService::class)->awardXp($user, 'badge_unlocked', $badge->xp_reward, [
                'badge_id' => $badge->id,
            ]);
        }
        
        event(new BadgeUnlocked($user, $badge));
    }
}
```

## 3. Rewards & Perks

### Available Rewards

| Reward                   | Cost (XP) | Type        | Description                        |
|--------------------------|-----------|-------------|------------------------------------|
| Profile Theme            | 500       | Cosmetic    | Custom profile color theme         |
| Pinned Hunt              | 200       | Feature     | Pin a Hunt to profile top          |
| Advanced Filters         | 1000      | Feature     | Unlock advanced search filters     |
| Extra Highlight Slot     | 800       | Feature     | Highlight one more project         |
| Featured in Explore      | 1500      | Boost       | 7 days featured spot               |
| Custom Badge             | 2000      | Cosmetic    | Design your own badge              |

### Database Schema

```sql
-- Rewards Definition
CREATE TABLE rewards (
    id BIGSERIAL PRIMARY KEY,
    key VARCHAR(50) UNIQUE NOT NULL,
    label VARCHAR(100) NOT NULL,
    description TEXT,
    type VARCHAR(20) NOT NULL, -- 'cosmetic', 'feature', 'boost'
    cost_xp INTEGER NOT NULL,
    payload JSONB, -- Configuration for the reward
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- User Rewards (Claimed)
CREATE TABLE user_rewards (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    reward_id BIGINT NOT NULL,
    unlocked_at TIMESTAMP DEFAULT NOW(),
    claimed_at TIMESTAMP,
    expires_at TIMESTAMP, -- For time-limited rewards
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reward_id) REFERENCES rewards(id) ON DELETE CASCADE
);
```

### Implementation

```php
public function claimReward(User $user, Reward $reward): bool
{
    // Check if user has enough XP
    if ($user->xp < $reward->cost_xp) {
        throw new InsufficientXpException();
    }
    
    // Check if already claimed
    if ($user->hasReward($reward)) {
        throw new RewardAlreadyClaimedException();
    }
    
    // Deduct XP
    $user->decrement('xp', $reward->cost_xp);
    
    // Unlock reward
    $user->rewards()->attach($reward->id, [
        'unlocked_at' => now(),
        'claimed_at' => now(),
        'expires_at' => $reward->duration ? now()->addDays($reward->duration) : null,
    ]);
    
    return true;
}
```

## 4. Leaderboards

### Types

1. **All-time**: Total XP ranking
2. **Monthly**: XP earned in current month
3. **Weekly**: XP earned in current week

### Implementation

```php
public function getLeaderboard(string $period = 'all-time', int $limit = 50): Collection
{
    $query = User::select('id', 'name', 'avatar', 'level', 'xp');
    
    switch ($period) {
        case 'weekly':
            $start = now()->startOfWeek();
            break;
        case 'monthly':
            $start = now()->startOfMonth();
            break;
        default:
            $start = null;
    }
    
    if ($start) {
        $query->withSum(['xpEvents as period_xp' => fn($q) => $q->where('created_at', '>=', $start)], 'points')
              ->orderByDesc('period_xp');
    } else {
        $query->orderByDesc('xp');
    }
    
    return $query->limit($limit)->get();
}
```

### Frontend Display

```tsx
export function Leaderboard({ period = 'all-time' }: { period?: string }) {
    const [leaders, setLeaders] = useState([]);
    
    useEffect(() => {
        axios.get(`/api/leaderboard?period=${period}`)
            .then(res => setLeaders(res.data));
    }, [period]);
    
    return (
        <div className="space-y-2">
            {leaders.map((user, index) => (
                <div key={user.id} className="flex items-center gap-4 p-4 bg-card rounded-lg">
                    <span className="text-2xl font-bold text-muted-foreground">
                        #{index + 1}
                    </span>
                    <Avatar src={user.avatar} />
                    <div className="flex-1">
                        <p className="font-semibold">{user.name}</p>
                        <p className="text-sm text-muted-foreground">Level {user.level}</p>
                    </div>
                    <span className="font-bold">{user.xp} XP</span>
                </div>
            ))}
        </div>
    );
}
```

## 5. Anti-Abuse System

### Detection Heuristics

```php
namespace App\Services;

class XpAbuseDetector
{
    protected array $rules = [
        'burst_creation' => [
            'window' => 60, // seconds
            'max_actions' => 5,
            'penalty' => 'throttle',
        ],
        'duplicate_content' => [
            'similarity_threshold' => 0.9,
            'penalty' => 'diminish',
        ],
        'mutual_farming' => [
            'reciprocal_threshold' => 10,
            'penalty' => 'flag',
        ],
    ];
    
    public function validateXpGrant(User $user, string $eventType, int $points): array
    {
        // Check burst creation
        if ($this->detectBurst($user, $eventType)) {
            return [
                'granted' => false,
                'status' => 'throttled',
                'reason' => 'burst_creation',
                'points' => 0,
                'throttle_until' => now()->addMinutes(15),
            ];
        }
        
        // Check diminishing returns
        $dailyEvents = $this->getDailyEventCount($user, $eventType);
        if ($dailyEvents > 20) {
            $multiplier = max(0.1, 1 - ($dailyEvents - 20) * 0.05);
            return [
                'granted' => true,
                'status' => 'diminished',
                'reason' => 'daily_limit',
                'points' => (int) ($points * $multiplier),
            ];
        }
        
        return [
            'granted' => true,
            'status' => 'granted',
            'points' => $points,
        ];
    }
    
    protected function detectBurst(User $user, string $eventType): bool
    {
        $rule = $this->rules['burst_creation'];
        
        $recentCount = XpEvent::where('user_id', $user->id)
            ->where('event_type', $eventType)
            ->where('created_at', '>=', now()->subSeconds($rule['window']))
            ->count();
        
        return $recentCount >= $rule['max_actions'];
    }
}
```

## 6. Gamification Dashboard

### Location
**Settings → Gamification**

### Sections

1. **Progress Tracker**: Visual representation of completed core actions
2. **XP & Level**: Current level, XP bar, next level info
3. **Badges Grid**: Locked and unlocked badges
4. **Rewards Shop**: Available perks with costs
5. **Leaderboard Position**: User's rank

### Frontend Implementation

```tsx
export default function GamificationDashboard() {
    const { user, badges, rewards, leaderboardPosition } = usePage().props;
    
    return (
        <div className="space-y-8">
            <section>
                <h2>Your Progress</h2>
                <XpBar 
                    currentXp={user.xp} 
                    currentLevel={user.level}
                    nextLevelXp={calculateNextLevelXp(user.level)}
                />
            </section>
            
            <section>
                <h2>Badges</h2>
                <div className="grid grid-cols-3 gap-4">
                    {badges.map(badge => (
                        <BadgeCard 
                            key={badge.id} 
                            badge={badge}
                            unlocked={user.badges.includes(badge.id)}
                        />
                    ))}
                </div>
            </section>
            
            <section>
                <h2>Rewards</h2>
                <div className="space-y-4">
                    {rewards.map(reward => (
                        <RewardCard 
                            key={reward.id}
                            reward={reward}
                            canClaim={user.xp >= reward.cost_xp}
                            claimed={user.rewards.includes(reward.id)}
                        />
                    ))}
                </div>
            </section>
        </div>
    );
}
```

## Testing

```php
test('user earns xp when creating a hunt', function () {
    $user = User::factory()->create(['xp' => 0]);
    
    $hunt = Hunt::factory()->create(['user_id' => $user->id]);
    
    $user->refresh();
    expect($user->xp)->toBe(10);
});

test('user levels up when reaching threshold', function () {
    $user = User::factory()->create(['xp' => 90, 'level' => 1]);
    
    app(XpService::class)->awardXp($user, 'test', 20);
    
    $user->refresh();
    expect($user->level)->toBe(2);
});

test('burst detection prevents abuse', function () {
    $user = User::factory()->create();
    
    // Create 6 hunts rapidly
    for ($i = 0; $i < 6; $i++) {
        app(XpService::class)->awardXp($user, 'hunt_created', 10);
    }
    
    // Last event should be throttled
    $lastDecision = XpDecision::latest()->first();
    expect($lastDecision->decision)->toBe('throttled');
});
```

## Configuration

```php
// config/gamification.php

return [
    'xp' => [
        'hunt_created' => 10,
        'comment_received' => 5,
        'like_received' => 2,
        'follow_given' => 3,
        'message_sent' => 1,
        'search_used' => 1,
        'profile_completed' => 50,
    ],
    
    'level_thresholds' => [
        1 => 0,
        2 => 100,
        3 => 250,
        // ... more levels
    ],
    
    'anti_abuse' => [
        'burst_window' => 60, // seconds
        'burst_max' => 5,
        'daily_diminish_threshold' => 20,
        'diminish_rate' => 0.05,
    ],
];
```

## Related Documentation

- [User Profiles](./03-user-profiles.md) - Profile completion tracking
- [Social Features](./05-social-features.md) - Follow, like, comment
- [Testing](./10-testing.md) - Testing guidelines
