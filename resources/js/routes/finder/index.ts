import { queryParams, type RouteDefinition, type RouteQueryOptions } from './../../wayfinder';
/**
 * @see \App\Http\Controllers\FinderController::__invoke
 * @see app/Http/Controllers/FinderController.php:23
 * @route '/finder'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/finder',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\FinderController::__invoke
 * @see app/Http/Controllers/FinderController.php:23
 * @route '/finder'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\FinderController::__invoke
 * @see app/Http/Controllers/FinderController.php:23
 * @route '/finder'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\FinderController::__invoke
 * @see app/Http/Controllers/FinderController.php:23
 * @route '/finder'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

const finder = {
    index: Object.assign(index, index),
};

export default finder;
