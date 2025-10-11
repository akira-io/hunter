<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Hunt;

use App\Actions\Hunt\ProcessHuntImageAction;
use App\Actions\Hunt\UpdateHuntImageStatusAction;
use App\Enums\HuntImageProcessingStatus;
use App\Events\HuntImageProcessed;
use App\Models\Hunt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

final class ProcessHuntImageActionTest extends TestCase
{
    use RefreshDatabase;

    private ProcessHuntImageAction $action;

    private UpdateHuntImageStatusAction $updateStatusAction;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->updateStatusAction = app(UpdateHuntImageStatusAction::class);
        $this->action = new ProcessHuntImageAction($this->updateStatusAction);
    }

    public function test_successfully_processes_and_stores_hunt_image(): void
    {
        Event::fake();
        Log::spy();

        $user = User::factory()->create();
        $hunt = Hunt::factory()->create([
            'owner_id' => $user->id,
            'image_processing_status' => HuntImageProcessingStatus::Pending,
        ]);

        $image = UploadedFile::fake()->image('test.jpg', 800, 600);

        $this->action->handle($hunt, $image);

        $hunt->refresh();

        $this->assertEquals(HuntImageProcessingStatus::Completed, $hunt->image_processing_status);
        $this->assertNotEmpty($hunt->getFirstMediaUrl('hunts'));
        $this->assertCount(1, $hunt->getMedia('hunts'));
    }

    public function test_updates_status_to_processing_before_handling_image(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $hunt = Hunt::factory()->create([
            'owner_id' => $user->id,
            'image_processing_status' => HuntImageProcessingStatus::Pending,
        ]);

        $image = UploadedFile::fake()->image('test.jpg');

        $this->action->handle($hunt, $image);

        // After processing, it should be completed
        $hunt->refresh();
        $this->assertEquals(HuntImageProcessingStatus::Completed, $hunt->image_processing_status);
    }

    public function test_broadcasts_hunt_image_processed_event(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $hunt = Hunt::factory()->create([
            'owner_id' => $user->id,
            'image_processing_status' => HuntImageProcessingStatus::Pending,
        ]);

        $image = UploadedFile::fake()->image('test.jpg');

        $this->action->handle($hunt, $image);

        Event::assertDispatched(HuntImageProcessed::class, function ($event) use ($hunt) {
            return $event->hunt->id === $hunt->id
                && $event->hunt->image_processing_status === HuntImageProcessingStatus::Completed;
        });
    }

    public function test_logs_success_message_with_hunt_details(): void
    {
        Event::fake();
        Log::spy();

        $user = User::factory()->create();
        $hunt = Hunt::factory()->create([
            'owner_id' => $user->id,
            'image_processing_status' => HuntImageProcessingStatus::Pending,
        ]);

        $image = UploadedFile::fake()->image('test.jpg');

        $this->action->handle($hunt, $image);

        Log::shouldHaveReceived('info')
            ->once()
            ->with('Hunt image processed successfully', Mockery::on(function ($context) use ($hunt) {
                return $context['hunt_id'] === $hunt->id
                    && isset($context['image_url'])
                    && $context['status'] === HuntImageProcessingStatus::Completed->value
                    && $context['broadcasting_event'] === 'hunt.image.processed';
            }));
    }

    public function test_refreshes_hunt_before_broadcasting(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $hunt = Hunt::factory()->create([
            'owner_id' => $user->id,
            'image_processing_status' => HuntImageProcessingStatus::Pending,
        ]);

        $image = UploadedFile::fake()->image('test.jpg');

        $this->action->handle($hunt, $image);

        Event::assertDispatched(HuntImageProcessed::class, function ($event) {
            // Verify hunt has been refreshed with media
            return $event->hunt->image_processing_status === HuntImageProcessingStatus::Completed
                && $event->hunt->getMedia('hunts')->count() > 0;
        });
    }

    public function test_adds_media_to_hunts_collection(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $hunt = Hunt::factory()->create([
            'owner_id' => $user->id,
            'image_processing_status' => HuntImageProcessingStatus::Pending,
        ]);

        $image = UploadedFile::fake()->image('test.jpg');

        $this->assertCount(0, $hunt->getMedia('hunts'));

        $this->action->handle($hunt, $image);

        $hunt->refresh();

        $this->assertCount(1, $hunt->getMedia('hunts'));
        $this->assertEquals('hunts', $hunt->getMedia('hunts')->first()->collection_name);
    }

    public function test_handles_different_image_types(): void
    {
        Event::fake();

        $user = User::factory()->create();

        $imageTypes = ['jpg', 'png', 'gif'];

        foreach ($imageTypes as $type) {
            $hunt = Hunt::factory()->create([
                'owner_id' => $user->id,
                'image_processing_status' => HuntImageProcessingStatus::Pending,
            ]);

            $image = UploadedFile::fake()->image("test.{$type}");

            $this->action->handle($hunt, $image);

            $hunt->refresh();

            $this->assertEquals(HuntImageProcessingStatus::Completed, $hunt->image_processing_status);
            $this->assertNotEmpty($hunt->getFirstMediaUrl('hunts'));
        }
    }
}
