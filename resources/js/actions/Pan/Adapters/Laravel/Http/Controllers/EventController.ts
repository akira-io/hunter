import { queryParams, type RouteDefinition, type RouteQueryOptions } from './../../../../../../wayfinder';
/**
 * @see \Pan\Adapters\Laravel\Http\Controllers\EventController::store
 * @see vendor/panphp/pan/src/Adapters/Laravel/Http/Controllers/EventController.php:21
 * @route '/pan/events'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

store.definition = {
    methods: ['post'],
    url: '/pan/events',
} satisfies RouteDefinition<['post']>;

/**
 * @see \Pan\Adapters\Laravel\Http\Controllers\EventController::store
 * @see vendor/panphp/pan/src/Adapters/Laravel/Http/Controllers/EventController.php:21
 * @route '/pan/events'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options);
};

/**
 * @see \Pan\Adapters\Laravel\Http\Controllers\EventController::store
 * @see vendor/panphp/pan/src/Adapters/Laravel/Http/Controllers/EventController.php:21
 * @route '/pan/events'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
});

const EventController = { store };

export default EventController;
