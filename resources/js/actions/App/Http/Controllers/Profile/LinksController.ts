import { queryParams, type RouteDefinition, type RouteQueryOptions } from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Profile\LinksController::__invoke
 * @see app/Http/Controllers/Profile/LinksController.php:20
 * @route '/profile/links'
 */
const LinksController = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: LinksController.url(options),
    method: 'patch',
});

LinksController.definition = {
    methods: ['patch'],
    url: '/profile/links',
} satisfies RouteDefinition<['patch']>;

/**
 * @see \App\Http\Controllers\Profile\LinksController::__invoke
 * @see app/Http/Controllers/Profile/LinksController.php:20
 * @route '/profile/links'
 */
LinksController.url = (options?: RouteQueryOptions) => {
    return LinksController.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Profile\LinksController::__invoke
 * @see app/Http/Controllers/Profile/LinksController.php:20
 * @route '/profile/links'
 */
LinksController.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: LinksController.url(options),
    method: 'patch',
});

export default LinksController;
