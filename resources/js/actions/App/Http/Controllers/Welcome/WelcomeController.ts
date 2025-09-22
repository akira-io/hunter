import { queryParams, type RouteDefinition, type RouteQueryOptions } from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Welcome\WelcomeController::index
 * @see app/Http/Controllers/Welcome/WelcomeController.php:20
 * @route '/'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Welcome\WelcomeController::index
 * @see app/Http/Controllers/Welcome/WelcomeController.php:20
 * @route '/'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Welcome\WelcomeController::index
 * @see app/Http/Controllers/Welcome/WelcomeController.php:20
 * @route '/'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\Welcome\WelcomeController::index
 * @see app/Http/Controllers/Welcome/WelcomeController.php:20
 * @route '/'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

const WelcomeController = { index };

export default WelcomeController;
