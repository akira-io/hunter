import { queryParams, type RouteDefinition, type RouteQueryOptions } from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Followable\UnFollowController::__invoke
 * @see app/Http/Controllers/Followable/UnFollowController.php:23
 * @route '/followable/unfollow'
 */
const UnFollowController = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: UnFollowController.url(options),
    method: 'post',
});

UnFollowController.definition = {
    methods: ['post'],
    url: '/followable/unfollow',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Followable\UnFollowController::__invoke
 * @see app/Http/Controllers/Followable/UnFollowController.php:23
 * @route '/followable/unfollow'
 */
UnFollowController.url = (options?: RouteQueryOptions) => {
    return UnFollowController.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Followable\UnFollowController::__invoke
 * @see app/Http/Controllers/Followable/UnFollowController.php:23
 * @route '/followable/unfollow'
 */
UnFollowController.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: UnFollowController.url(options),
    method: 'post',
});

export default UnFollowController;
