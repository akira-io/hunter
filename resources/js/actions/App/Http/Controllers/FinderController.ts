import { queryParams, type RouteDefinition, type RouteQueryOptions } from './../../../../wayfinder';
/**
 * @see \App\Http\Controllers\FinderController::__invoke
 * @see app/Http/Controllers/FinderController.php:23
 * @route '/finder'
 */
const FinderController = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: FinderController.url(options),
    method: 'get',
});

FinderController.definition = {
    methods: ['get', 'head'],
    url: '/finder',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\FinderController::__invoke
 * @see app/Http/Controllers/FinderController.php:23
 * @route '/finder'
 */
FinderController.url = (options?: RouteQueryOptions) => {
    return FinderController.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\FinderController::__invoke
 * @see app/Http/Controllers/FinderController.php:23
 * @route '/finder'
 */
FinderController.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: FinderController.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\FinderController::__invoke
 * @see app/Http/Controllers/FinderController.php:23
 * @route '/finder'
 */
FinderController.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: FinderController.url(options),
    method: 'head',
});

export default FinderController;
