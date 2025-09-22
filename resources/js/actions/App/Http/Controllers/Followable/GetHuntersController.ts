import { queryParams, type RouteDefinition, type RouteQueryOptions } from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Followable\GetHuntersController::__invoke
 * @see app/Http/Controllers/Followable/GetHuntersController.php:21
 * @route '/followable/followers'
 */
const GetHuntersController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: GetHuntersController.url(options),
    method: 'get',
});

GetHuntersController.definition = {
    methods: ['get', 'head'],
    url: '/followable/followers',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Followable\GetHuntersController::__invoke
 * @see app/Http/Controllers/Followable/GetHuntersController.php:21
 * @route '/followable/followers'
 */
GetHuntersController.url = (options?: RouteQueryOptions) => {
    return GetHuntersController.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Followable\GetHuntersController::__invoke
 * @see app/Http/Controllers/Followable/GetHuntersController.php:21
 * @route '/followable/followers'
 */
GetHuntersController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: GetHuntersController.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\Followable\GetHuntersController::__invoke
 * @see app/Http/Controllers/Followable/GetHuntersController.php:21
 * @route '/followable/followers'
 */
GetHuntersController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: GetHuntersController.url(options),
    method: 'head',
});

export default GetHuntersController;
