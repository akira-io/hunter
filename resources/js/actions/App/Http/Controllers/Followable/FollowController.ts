import { queryParams, type RouteDefinition, type RouteQueryOptions } from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Followable\FollowController::__invoke
 * @see app/Http/Controllers/Followable/FollowController.php:24
 * @route '/followable/follow'
 */
const FollowController = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: FollowController.url(options),
    method: 'post',
});

FollowController.definition = {
    methods: ['post'],
    url: '/followable/follow',
} satisfies RouteDefinition<['post']>;

/**
 * @see \App\Http\Controllers\Followable\FollowController::__invoke
 * @see app/Http/Controllers/Followable/FollowController.php:24
 * @route '/followable/follow'
 */
FollowController.url = (options?: RouteQueryOptions) => {
    return FollowController.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Followable\FollowController::__invoke
 * @see app/Http/Controllers/Followable/FollowController.php:24
 * @route '/followable/follow'
 */
FollowController.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: FollowController.url(options),
    method: 'post',
});

export default FollowController;
