<?php
declare(strict_types=1);

/**
 * NOTE: Detected testing library/framework: PestPHP (Laravel).
 * File focuses on App\Http\Controllers\PublicProfileController::show().
 * It validates happy paths, edge/failure cases, mocks external dependencies, and inspects the Inertia JSON page object.
 */

use Akira\Followable\Exceptions\FollowableTraitNotFoundException;
use App\Actions\Followable\GetHuntingsAction;
use App\Actions\User\UserProfileAction;
use App\Http\Controllers\PublicProfileController;
use App\Http\Resources\Hunt\HuntResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;

afterEach(function () {
    Mockery::close();
});

test('show returns Inertia JSON with expected keys and uses auth user to attach statuses', function () {
    $authUser = Mockery::mock(User::class);
    $user = Mockery::mock(User::class);

    // hunts() -> latest() -> paginate()
    $huntsPaginator = (object)['tag' => 'huntsPaginator'];
    $huntsQuery = Mockery::mock('stdClass');
    $huntsQuery->shouldReceive('latest')->once()->andReturnSelf();
    $huntsQuery->shouldReceive('paginate')->once()->andReturn($huntsPaginator);
    $user->shouldReceive('hunts')->once()->andReturn($huntsQuery);

    // followers() -> latest() -> paginate()
    $huntersPaginator = (object)['tag' => 'huntersPaginator'];
    $followersQuery = Mockery::mock('stdClass');
    $followersQuery->shouldReceive('latest')->once()->andReturnSelf();
    $followersQuery->shouldReceive('paginate')->once()->andReturn($huntersPaginator);
    $user->shouldReceive('followers')->once()->andReturn($followersQuery);

    // Actions
    $huntings = ['h1', 'h2'];
    $huntingsAction = Mockery::mock(GetHuntingsAction::class);
    $huntingsAction->shouldReceive('handle')->once()->with($user)->andReturn($huntings);

    $userProfile = ['id' => 42, 'name' => 'Test User'];
    $userProfileAction = Mockery::mock(UserProfileAction::class);
    $userProfileAction->shouldReceive('handle')->once()->with($user)->andReturn($userProfile);

    // Auth user attaches statuses
    $likedHunts = ['liked1', 'liked2'];
    $authUser->shouldReceive('attachLikeStatus')->once()->with($huntsPaginator)->andReturn($likedHunts);

    $huntersWithStatus = ['u1', 'u2'];
    $authUser->shouldReceive('attachFollowStatus')->once()->with($huntersPaginator)->andReturn($huntersWithStatus);

    $huntingsWithStatus = ['f1', 'f2'];
    $authUser->shouldReceive('attachFollowStatus')->once()->with($huntings)->andReturn($huntingsWithStatus);

    // Request with X-Inertia header and auth resolver
    $request = Request::create('/public-profile/1', 'GET', [], [], [], ['HTTP_X_INERTIA' => 'true']);
    $request->headers->set('X-Inertia', 'true');
    $request->setUserResolver(fn () => $authUser);

    $controller = new PublicProfileController();

    $inertiaResponse = $controller->show($request, $user, $huntingsAction, $userProfileAction);
    $json = $inertiaResponse->toResponse($request);

    expect($json)->toBeInstanceOf(JsonResponse::class);
    expect($json->headers->get('X-Inertia'))->toBe('true');

    $page = $json->getData(true);

    expect($page['component'])->toBe('public-profile');
    expect($page['props'])->toBeArray()->toHaveKeys(['user','hunts','hunters','huntings']);

    expect($page['props']['user'])->toEqual($userProfile);
    expect($page['props']['hunters'])->toEqual($huntersWithStatus);
    expect($page['props']['huntings'])->toEqual($huntingsWithStatus);
    // 'hunts' is a Resource collection; shape can vary (wrapping). Just ensure it exists.
    expect($page['props']['hunts'])->not()->toBeNull();
});

test('show bubbles FollowableTraitNotFoundException', function () {
    $authUser = Mockery::mock(User::class);
    $user = Mockery::mock(User::class);

    $huntsQuery = Mockery::mock('stdClass');
    $huntsQuery->shouldReceive('latest')->andReturnSelf();
    $huntsQuery->shouldReceive('paginate')->andReturn((object)[]);
    $user->shouldReceive('hunts')->andReturn($huntsQuery);

    $followersQuery = Mockery::mock('stdClass');
    $followersQuery->shouldReceive('latest')->andReturnSelf();
    $followersQuery->shouldReceive('paginate')->andReturn((object)[]);
    $user->shouldReceive('followers')->andReturn($followersQuery);

    $huntingsAction = Mockery::mock(GetHuntingsAction::class);
    $huntingsAction->shouldReceive('handle')->andReturn([]);

    $userProfileAction = Mockery::mock(UserProfileAction::class);
    $userProfileAction->shouldReceive('handle')->once()->with($user)->andThrow(new FollowableTraitNotFoundException('missing'));

    $authUser->shouldReceive('attachLikeStatus')->andReturn([]);
    $authUser->shouldReceive('attachFollowStatus')->andReturn([]);

    $request = Request::create('/public-profile/1', 'GET');
    $request->setUserResolver(fn () => $authUser);

    $controller = new PublicProfileController();

    expect(fn () => $controller->show($request, $user, $huntingsAction, $userProfileAction))
        ->toThrow(FollowableTraitNotFoundException::class);
});

test('show bubbles unexpected throwable from huntings action', function () {
    $authUser = Mockery::mock(User::class);
    $user = Mockery::mock(User::class);

    $huntsQuery = Mockery::mock('stdClass');
    $huntsQuery->shouldReceive('latest')->andReturnSelf();
    $huntsQuery->shouldReceive('paginate')->andReturn((object)[]);
    $user->shouldReceive('hunts')->andReturn($huntsQuery);

    $followersQuery = Mockery::mock('stdClass');
    $followersQuery->shouldReceive('latest')->andReturnSelf();
    $followersQuery->shouldReceive('paginate')->andReturn((object)[]);
    $user->shouldReceive('followers')->andReturn($followersQuery);

    $huntingsAction = Mockery::mock(GetHuntingsAction::class);
    $huntingsAction->shouldReceive('handle')->once()->with($user)->andThrow(new RuntimeException('Oops'));

    $userProfileAction = Mockery::mock(UserProfileAction::class);
    $userProfileAction->shouldReceive('handle')->andReturn(['id' => 1]);

    $authUser->shouldReceive('attachLikeStatus')->andReturn([]);
    $authUser->shouldReceive('attachFollowStatus')->andReturn([]);

    $request = Request::create('/public-profile/1', 'GET');
    $request->setUserResolver(fn () => $authUser);

    $controller = new PublicProfileController();

    expect(fn () => $controller->show($request, $user, $huntingsAction, $userProfileAction))
        ->toThrow(RuntimeException::class);
});