import { queryParams, type RouteDefinition, type RouteQueryOptions } from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Auth\GoogleAuthController::index
 * @see app/Http/Controllers/Auth/GoogleAuthController.php:21
 * @route '/auth/google'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

index.definition = {
    methods: ['get', 'head'],
    url: '/auth/google',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Auth\GoogleAuthController::index
 * @see app/Http/Controllers/Auth/GoogleAuthController.php:21
 * @route '/auth/google'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Auth\GoogleAuthController::index
 * @see app/Http/Controllers/Auth/GoogleAuthController.php:21
 * @route '/auth/google'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\Auth\GoogleAuthController::index
 * @see app/Http/Controllers/Auth/GoogleAuthController.php:21
 * @route '/auth/google'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
});

/**
 * @see \App\Http\Controllers\Auth\GoogleAuthController::store
 * @see app/Http/Controllers/Auth/GoogleAuthController.php:32
 * @route '/auth/google/callback'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: store.url(options),
    method: 'get',
});

store.definition = {
    methods: ['get', 'head'],
    url: '/auth/google/callback',
} satisfies RouteDefinition<['get', 'head']>;

/**
 * @see \App\Http\Controllers\Auth\GoogleAuthController::store
 * @see app/Http/Controllers/Auth/GoogleAuthController.php:32
 * @route '/auth/google/callback'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Auth\GoogleAuthController::store
 * @see app/Http/Controllers/Auth/GoogleAuthController.php:32
 * @route '/auth/google/callback'
 */
store.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: store.url(options),
    method: 'get',
});

/**
 * @see \App\Http\Controllers\Auth\GoogleAuthController::store
 * @see app/Http/Controllers/Auth/GoogleAuthController.php:32
 * @route '/auth/google/callback'
 */
store.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: store.url(options),
    method: 'head',
});

const GoogleAuthController = { index, store };

export default GoogleAuthController;
