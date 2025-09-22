import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Likeable\ToggleHuntLikeController::store
* @see app/Http/Controllers/Likeable/ToggleHuntLikeController.php:21
* @route '/likeable/{hunt}'
*/
export const store = (args: { hunt: number | { id: number } } | [hunt: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/likeable/{hunt}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Likeable\ToggleHuntLikeController::store
* @see app/Http/Controllers/Likeable/ToggleHuntLikeController.php:21
* @route '/likeable/{hunt}'
*/
store.url = (args: { hunt: number | { id: number } } | [hunt: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { hunt: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { hunt: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            hunt: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        hunt: typeof args.hunt === 'object'
        ? args.hunt.id
        : args.hunt,
    }

    return store.definition.url
            .replace('{hunt}', parsedArgs.hunt.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Likeable\ToggleHuntLikeController::store
* @see app/Http/Controllers/Likeable/ToggleHuntLikeController.php:21
* @route '/likeable/{hunt}'
*/
store.post = (args: { hunt: number | { id: number } } | [hunt: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

const ToggleHuntLikeController = { store }

export default ToggleHuntLikeController