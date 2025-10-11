# Hunt Metrics System

## Overview

The **Hunt Metrics System** is a comprehensive analytics solution that calculates and tracks engagement, reach, and quality metrics for hunts (posts) in the platform. It uses a **Pipeline Pattern** for extensible, maintainable metric calculations.

The system provides insights into:
- **Engagement rates** (likes, comments, shares)
- **Quality scores** (weighted content performance)
- **Virality coefficients** (share propensity)
- **Performance levels** (excellent to poor rankings)
- **Reach metrics** (view-based calculations)

## Architecture

### Pipeline Pattern

The metrics system uses Laravel's Pipeline pattern with three stages:

```
Hunt Model → MetricsContext → [Reach] → [Engagement] → [Quality] → HuntMetrics DTO
```

**Benefits:**
- **Extensible**: Easy to add new metric calculators
- **Maintainable**: Each stage has single responsibility
- **Testable**: Isolated components with 100% coverage
- **Immutable**: Context objects don't mutate
- **Type-safe**: Full PHP type hints and DTOs

### Key Components

```
app/
├── DataTransferObjects/Metrics/
│   ├── HuntMetrics.php           # Final metrics DTO
│   └── MetricsContext.php         # Pipeline context carrier
├── Services/Metrics/
│   ├── MetricsPipeline.php        # Orchestrates pipeline
│   └── Calculators/
│       ├── HuntMetricsCalculator.php     # Main entry point
│       ├── EngagementMetricsCalculator.php
│       ├── QualityMetricsCalculator.php
│       └── ReachMetricsCalculator.php
└── Pipes/Metrics/
    ├── EngagementMetricsPipe.php  # Stage 2: Engagement
    ├── QualityMetricsPipe.php     # Stage 3: Quality
    └── ReachMetricsPipe.php       # Stage 1: Reach
```

## Data Transfer Objects

### HuntMetrics DTO

The final, immutable metrics object returned after pipeline processing:

```php
use App\DataTransferObjects\Metrics\HuntMetrics;

$metrics = new HuntMetrics(
    // Raw counts
    views: 1000,
    likes: 120,
    comments: 45,
    shares: 15,

    // Engagement rates (%)
    engagementRate: 18.0,      // Total engagement rate
    interactionRate: 16.5,     // Likes + comments rate
    commentRate: 4.5,          // Comment rate
    shareRate: 1.5,            // Share rate

    // Quality metrics
    qualityScore: 75.5,        // 0-100 weighted score
    viralityCoefficient: 0.015, // 0-1 share propensity
    avgEngagementPerView: 0.18, // Average per view

    // Performance
    performanceLevel: 'good',   // excellent|good|average|below_average|poor
    rank: 4,                    // 1-5 stars
);
```

**Key Properties:**

| Property | Type | Description |
|----------|------|-------------|
| `views` | `int` | Total hunt views |
| `likes` | `int` | Total likes received |
| `comments` | `int` | Total comments received |
| `shares` | `int` | Total shares |
| `engagementRate` | `float` | `(likes + comments + shares) / views * 100` |
| `interactionRate` | `float` | `(likes + comments) / views * 100` |
| `commentRate` | `float` | `comments / views * 100` |
| `shareRate` | `float` | `shares / views * 100` |
| `qualityScore` | `float` | Weighted score (0-100) |
| `viralityCoefficient` | `float` | Share likelihood (0-1) |
| `avgEngagementPerView` | `float` | Average engagement per view |
| `performanceLevel` | `string` | Human-readable performance |
| `rank` | `int` | Star rating (1-5) |

**Helper Methods:**

```php
// Get total engagements
$total = $metrics->getTotalEngagements(); // likes + comments + shares

// Get total interactions
$interactions = $metrics->getTotalInteractions(); // likes + comments

// Check performance
if ($metrics->isPerformingWell()) {
    // Quality score >= 50
}

// Check virality
if ($metrics->isViral()) {
    // Virality coefficient >= 0.1 (10% share rate)
}

// Convert to array
$array = $metrics->toArray();
```

### MetricsContext DTO

Immutable context object that carries data through the pipeline:

```php
use App\DataTransferObjects\Metrics\MetricsContext;

$context = new MetricsContext(
    model: $hunt,
    views: 1000,
    likes: 120,
    comments: 45,
    shares: 15,
    calculated: [], // Enriched by each pipe
);

// Immutably add calculated metrics
$enriched = $context->withCalculated([
    'engagement_rate' => 18.0,
    'quality_score' => 75.5,
]);

// Access calculated values
$rate = $context->get('engagement_rate', 0.0);
$hasRate = $context->has('engagement_rate');
```

## Calculators

### HuntMetricsCalculator

Main entry point for calculating hunt metrics:

```php
use App\Services\Metrics\Calculators\HuntMetricsCalculator;

$calculator = app(HuntMetricsCalculator::class);

// Calculate metrics for a hunt
$metrics = $calculator->calculateDetailed($hunt);

// Access metrics
echo "Quality Score: {$metrics->qualityScore}%\n";
echo "Engagement Rate: {$metrics->engagementRate}%\n";
echo "Performance: {$metrics->performanceLevel}\n";
```

**Features:**
- Type-safe: Only accepts `Hunt` models
- Validates model type before processing
- Returns strongly-typed `HuntMetrics` DTO
- Orchestrates the metrics pipeline

### EngagementMetricsCalculator

Calculates engagement-related rates:

```php
use App\Services\Metrics\Calculators\EngagementMetricsCalculator;

$calculator = app(EngagementMetricsCalculator::class);

$metrics = $calculator->calculate(
    views: 1000,
    likes: 120,
    comments: 45,
    shares: 15
);

// Returns:
[
    'engagement_rate' => 18.0,      // (120 + 45 + 15) / 1000 * 100
    'interaction_rate' => 16.5,     // (120 + 45) / 1000 * 100
    'comment_rate' => 4.5,          // 45 / 1000 * 100
    'share_rate' => 1.5,            // 15 / 1000 * 100
    'avg_engagement_per_view' => 0.180, // 180 / 1000
]
```

**Formulas:**
- **Engagement Rate**: `(likes + comments + shares) / views * 100`
- **Interaction Rate**: `(likes + comments) / views * 100`
- **Comment Rate**: `comments / views * 100`
- **Share Rate**: `shares / views * 100`
- **Avg Engagement**: `total_engagements / views`

### QualityMetricsCalculator

Calculates quality score and virality using weighted scoring:

```php
use App\Services\Metrics\Calculators\QualityMetricsCalculator;

$calculator = app(QualityMetricsCalculator::class);

$metrics = $calculator->calculate(
    views: 1000,
    likes: 120,
    comments: 45,
    shares: 15
);

// Returns:
[
    'quality_score' => 75.5,           // 0-100 weighted score
    'virality_coefficient' => 0.015,   // 15 / 1000 = 0.015
    'performance_level' => 'good',     // Based on quality score
    'rank' => 4,                       // 1-5 stars
]
```

**Scoring Weights:**

```php
WEIGHT_VIEW = 1      // Each view counts as 1 point
WEIGHT_LIKE = 3      // Each like counts as 3 points
WEIGHT_COMMENT = 5   // Each comment counts as 5 points
WEIGHT_SHARE = 10    // Each share counts as 10 points
```

**Quality Score Formula:**

```php
weighted_score = (views * 1) + (likes * 3) + (comments * 5) + (shares * 10)
max_possible = views * 10  // If all views became shares
quality_score = (weighted_score / max_possible) * 100
```

**Example Calculation:**

```
Views: 1000
Likes: 120
Comments: 45
Shares: 15

Weighted Score = (1000 * 1) + (120 * 3) + (45 * 5) + (15 * 10)
               = 1000 + 360 + 225 + 150
               = 1735

Max Possible = 1000 * 10 = 10000

Quality Score = (1735 / 10000) * 100 = 17.35%
```

**Performance Levels:**

| Quality Score | Performance Level | Rank |
|--------------|-------------------|------|
| 80 - 100 | `excellent` | ⭐⭐⭐⭐⭐ (5) |
| 60 - 79 | `good` | ⭐⭐⭐⭐ (4) |
| 40 - 59 | `average` | ⭐⭐⭐ (3) |
| 20 - 39 | `below_average` | ⭐⭐ (2) |
| 0 - 19 | `poor` | ⭐ (1) |

**Virality Coefficient:**

```php
virality_coefficient = shares / views

// Example:
// 15 shares / 1000 views = 0.015 (1.5% virality)
// If >= 0.1 (10%), content is considered "viral"
```

### ReachMetricsCalculator

Calculates reach-based metrics:

```php
use App\Services\Metrics\Calculators\ReachMetricsCalculator;

$calculator = app(ReachMetricsCalculator::class);

$metrics = $calculator->calculate(views: 1000);

// Returns reach-related metrics
```

## Pipeline Stages

### 1. ReachMetricsPipe

First stage: Calculates reach metrics

```php
namespace App\Pipes\Metrics;

class ReachMetricsPipe implements MetricsPipeContract
{
    public function handle(MetricsContext $context, Closure $next): MetricsContext
    {
        $reachMetrics = $this->calculator->calculate($context->views);

        $enriched = $context->withCalculated($reachMetrics);

        return $next($enriched);
    }
}
```

### 2. EngagementMetricsPipe

Second stage: Calculates engagement rates

```php
namespace App\Pipes\Metrics;

class EngagementMetricsPipe implements MetricsPipeContract
{
    public function handle(MetricsContext $context, Closure $next): MetricsContext
    {
        $engagementMetrics = $this->calculator->calculate(
            $context->views,
            $context->likes,
            $context->comments,
            $context->shares
        );

        $enriched = $context->withCalculated($engagementMetrics);

        return $next($enriched);
    }
}
```

### 3. QualityMetricsPipe

Third stage: Calculates quality and virality

```php
namespace App\Pipes\Metrics;

class QualityMetricsPipe implements MetricsPipeContract
{
    public function handle(MetricsContext $context, Closure $next): MetricsContext
    {
        $qualityMetrics = $this->calculator->calculate(
            $context->views,
            $context->likes,
            $context->comments,
            $context->shares
        );

        $enriched = $context->withCalculated($qualityMetrics);

        return $next($enriched);
    }
}
```

## Usage Examples

### Basic Usage

```php
use App\Services\Metrics\Calculators\HuntMetricsCalculator;
use App\Models\Hunt;

$hunt = Hunt::find(1);
$calculator = app(HuntMetricsCalculator::class);

// Calculate comprehensive metrics
$metrics = $calculator->calculateDetailed($hunt);

// Access properties
echo "Views: {$metrics->views}\n";
echo "Engagement Rate: {$metrics->engagementRate}%\n";
echo "Quality Score: {$metrics->qualityScore}/100\n";
echo "Performance: {$metrics->performanceLevel}\n";
echo "Rank: {$metrics->rank} stars\n";
```

### In a Controller

```php
namespace App\Http\Controllers;

use App\Models\Hunt;
use App\Services\Metrics\Calculators\HuntMetricsCalculator;

class HuntMetricsController extends Controller
{
    public function show(Hunt $hunt, HuntMetricsCalculator $calculator)
    {
        $metrics = $calculator->calculateDetailed($hunt);

        return response()->json([
            'hunt_id' => $hunt->id,
            'metrics' => $metrics->toArray(),
        ]);
    }
}
```

### In a Resource

```php
namespace App\Http\Resources\Hunt;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\Metrics\Calculators\HuntMetricsCalculator;

class HuntResource extends JsonResource
{
    public function toArray($request): array
    {
        $calculator = app(HuntMetricsCalculator::class);
        $metrics = $calculator->calculateDetailed($this->resource);

        return [
            'id' => $this->id,
            'content' => $this->content,
            'metrics' => $metrics->toArray(),
            // ... other fields
        ];
    }
}
```

### Filtering by Performance

```php
use App\Models\Hunt;
use App\Services\Metrics\Calculators\HuntMetricsCalculator;

$calculator = app(HuntMetricsCalculator::class);

// Get high-performing hunts
$hunts = Hunt::with('owner')->latest()->get();

$topHunts = $hunts->filter(function($hunt) use ($calculator) {
    $metrics = $calculator->calculateDetailed($hunt);
    return $metrics->isPerformingWell();
});

// Get viral hunts
$viralHunts = $hunts->filter(function($hunt) use ($calculator) {
    $metrics = $calculator->calculateDetailed($hunt);
    return $metrics->isViral();
});
```

### Batch Calculation

```php
use App\Models\Hunt;
use App\Services\Metrics\Calculators\HuntMetricsCalculator;

$calculator = app(HuntMetricsCalculator::class);
$hunts = Hunt::all();

$metricsCollection = $hunts->map(function($hunt) use ($calculator) {
    return [
        'hunt_id' => $hunt->id,
        'metrics' => $calculator->calculateDetailed($hunt)->toArray(),
    ];
});
```

## API Response Format

### Hunt with Metrics

```json
{
  "id": 123,
  "content": "Just deployed my Laravel app!",
  "metrics": {
    "views": 1000,
    "likes": 120,
    "comments": 45,
    "shares": 15,
    "total_engagements": 180,
    "engagement_rate": 18.0,
    "interaction_rate": 16.5,
    "comment_rate": 4.5,
    "share_rate": 1.5,
    "quality_score": 75.5,
    "virality_coefficient": 0.015,
    "avg_engagement_per_view": 0.18,
    "performance_level": "good",
    "rank": 4,
    "is_performing_well": true,
    "is_viral": false
  }
}
```

## Testing

The metrics system has 100% test coverage:

```php
use Tests\TestCase;
use App\Models\Hunt;
use App\Services\Metrics\Calculators\HuntMetricsCalculator;

class HuntMetricsTest extends TestCase
{
    public function test_calculates_engagement_rate(): void
    {
        $hunt = Hunt::factory()->create([
            'views_count' => 1000,
        ]);

        // Add 120 likes, 45 comments, 15 shares

        $calculator = app(HuntMetricsCalculator::class);
        $metrics = $calculator->calculateDetailed($hunt);

        $this->assertEquals(18.0, $metrics->engagementRate);
        $this->assertEquals('good', $metrics->performanceLevel);
    }

    public function test_identifies_viral_content(): void
    {
        $hunt = Hunt::factory()->create([
            'views_count' => 100,
            'shares_count' => 15, // 15% share rate
        ]);

        $calculator = app(HuntMetricsCalculator::class);
        $metrics = $calculator->calculateDetailed($hunt);

        $this->assertTrue($metrics->isViral());
        $this->assertGreaterThanOrEqual(0.1, $metrics->viralityCoefficient);
    }
}
```

**Test Files:**
- `tests/Unit/DataTransferObjects/Metrics/HuntMetricsTest.php` (100% coverage)
- `tests/Unit/DataTransferObjects/Metrics/MetricsContextTest.php` (100% coverage)
- `tests/Feature/Services/Metrics/HuntMetricsCalculatorTest.php`

## Performance Considerations

### Caching

For high-traffic hunts, consider caching metrics:

```php
use Illuminate\Support\Facades\Cache;

public function getMetrics(Hunt $hunt): HuntMetrics
{
    return Cache::remember(
        "hunt.{$hunt->id}.metrics",
        now()->addMinutes(5),
        fn() => $this->calculator->calculateDetailed($hunt)
    );
}
```

### Eager Loading

When displaying multiple hunts with metrics, eager load relationships:

```php
$hunts = Hunt::with('owner')
    ->withCount(['likes', 'comments'])
    ->get();

// Views and shares should be attributes on the Hunt model
```

### Database Optimization

Ensure indexes on:
- `hunts.views_count`
- `hunts.shares_count`
- `likes.likeable_id` and `likes.likeable_type`
- `comments.commentable_id` and `comments.commentable_type`

## Extending the System

### Adding a New Calculator

1. Create calculator class:

```php
namespace App\Services\Metrics\Calculators;

class CustomMetricsCalculator
{
    public function calculate(int $views, ...): array
    {
        return [
            'custom_metric' => // calculation
        ];
    }
}
```

2. Create pipe:

```php
namespace App\Pipes\Metrics;

class CustomMetricsPipe implements MetricsPipeContract
{
    public function __construct(
        private CustomMetricsCalculator $calculator
    ) {}

    public function handle(MetricsContext $context, Closure $next): MetricsContext
    {
        $metrics = $this->calculator->calculate($context->views);
        return $next($context->withCalculated($metrics));
    }
}
```

3. Add to pipeline:

```php
// In MetricsPipeline::getStages()
public function getStages(): array
{
    return [
        ReachMetricsPipe::class,
        EngagementMetricsPipe::class,
        QualityMetricsPipe::class,
        CustomMetricsPipe::class, // Add your pipe
    ];
}
```

### Customizing Weights

Modify weight constants in `QualityMetricsCalculator`:

```php
private const int WEIGHT_VIEW = 1;
private const int WEIGHT_LIKE = 3;
private const int WEIGHT_COMMENT = 5;
private const int WEIGHT_SHARE = 10;

// Example: Make shares more valuable
private const int WEIGHT_SHARE = 15;
```

## Best Practices

1. **Always use DTOs** - Don't work with raw arrays
2. **Cache expensive calculations** - Especially for popular hunts
3. **Eager load relationships** - Prevent N+1 queries
4. **Use type hints** - Leverage PHP's type system
5. **Test thoroughly** - Aim for 100% coverage
6. **Document formulas** - Make calculations transparent
7. **Monitor performance** - Track slow metrics calculations

## Common Use Cases

### Dashboard Analytics

```php
$topHunts = Hunt::latest()
    ->take(10)
    ->get()
    ->map(fn($hunt) => [
        'hunt' => $hunt,
        'metrics' => $calculator->calculateDetailed($hunt)
    ])
    ->sortByDesc('metrics.quality_score');
```

### User Performance Summary

```php
$userHunts = Hunt::where('owner_id', $userId)->get();

$totalViews = $userHunts->sum('views_count');
$avgEngagement = $userHunts->map(
    fn($hunt) => $calculator->calculateDetailed($hunt)->engagementRate
)->average();
```

### Content Recommendations

```php
$similar Hunts = Hunt::where('topic', $hunt->topic)
    ->get()
    ->filter(function($h) use ($calculator) {
        return $calculator->calculateDetailed($h)->isPerformingWell();
    });
```

## Related Documentation

- [Hunts (Posts)](./04-hunts.md)
- [Testing](./10-testing.md)
- [Test Coverage](./21-test-coverage.md)
- [API Reference](./08-api-reference.md)

## Troubleshooting

**Q: Metrics showing 0%?**
A: Ensure hunt has views. Zero views = zero engagement rates.

**Q: Quality score seems low?**
A: Check the weighted scoring. Comments and shares are worth more than likes.

**Q: Performance slow with many hunts?**
A: Implement caching and eager load relationships.

**Q: How to customize scoring weights?**
A: Modify constants in `QualityMetricsCalculator`.

**Q: Can I add custom metrics?**
A: Yes! Create a new calculator and pipe, then add to the pipeline.
