import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Followable\FollowController::__invoke
* @see app/Http/Controllers/Followable/FollowController.php:24
* @route '/followable/follow'
*/
export const follow = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: follow.url(options),
    method: 'post',
})

follow.definition = {
    methods: ["post"],
    url: '/followable/follow',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Followable\FollowController::__invoke
* @see app/Http/Controllers/Followable/FollowController.php:24
* @route '/followable/follow'
*/
follow.url = (options?: RouteQueryOptions) => {
    return follow.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Followable\FollowController::__invoke
* @see app/Http/Controllers/Followable/FollowController.php:24
* @route '/followable/follow'
*/
follow.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: follow.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Followable\GetHuntersController::__invoke
* @see app/Http/Controllers/Followable/GetHuntersController.php:21
* @route '/followable/followers'
*/
export const followers = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: followers.url(options),
    method: 'get',
})

followers.definition = {
    methods: ["get","head"],
    url: '/followable/followers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Followable\GetHuntersController::__invoke
* @see app/Http/Controllers/Followable/GetHuntersController.php:21
* @route '/followable/followers'
*/
followers.url = (options?: RouteQueryOptions) => {
    return followers.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Followable\GetHuntersController::__invoke
* @see app/Http/Controllers/Followable/GetHuntersController.php:21
* @route '/followable/followers'
*/
followers.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: followers.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Followable\GetHuntersController::__invoke
* @see app/Http/Controllers/Followable/GetHuntersController.php:21
* @route '/followable/followers'
*/
followers.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: followers.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Followable\GetHuntingsController::__invoke
* @see app/Http/Controllers/Followable/GetHuntingsController.php:24
* @route '/followable/followings'
*/
export const followings = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: followings.url(options),
    method: 'get',
})

followings.definition = {
    methods: ["get","head"],
    url: '/followable/followings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Followable\GetHuntingsController::__invoke
* @see app/Http/Controllers/Followable/GetHuntingsController.php:24
* @route '/followable/followings'
*/
followings.url = (options?: RouteQueryOptions) => {
    return followings.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Followable\GetHuntingsController::__invoke
* @see app/Http/Controllers/Followable/GetHuntingsController.php:24
* @route '/followable/followings'
*/
followings.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: followings.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Followable\GetHuntingsController::__invoke
* @see app/Http/Controllers/Followable/GetHuntingsController.php:24
* @route '/followable/followings'
*/
followings.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: followings.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Followable\UnFollowController::__invoke
* @see app/Http/Controllers/Followable/UnFollowController.php:23
* @route '/followable/unfollow'
*/
export const unfollow = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: unfollow.url(options),
    method: 'post',
})

unfollow.definition = {
    methods: ["post"],
    url: '/followable/unfollow',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Followable\UnFollowController::__invoke
* @see app/Http/Controllers/Followable/UnFollowController.php:23
* @route '/followable/unfollow'
*/
unfollow.url = (options?: RouteQueryOptions) => {
    return unfollow.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Followable\UnFollowController::__invoke
* @see app/Http/Controllers/Followable/UnFollowController.php:23
* @route '/followable/unfollow'
*/
unfollow.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: unfollow.url(options),
    method: 'post',
})

const followable = {
    follow: Object.assign(follow, follow),
    followers: Object.assign(followers, followers),
    followings: Object.assign(followings, followings),
    unfollow: Object.assign(unfollow, unfollow),
}

export default followable