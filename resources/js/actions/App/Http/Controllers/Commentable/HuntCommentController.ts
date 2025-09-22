import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Commentable\HuntCommentController::store
* @see app/Http/Controllers/Commentable/HuntCommentController.php:26
* @route '/commentable/hunts/{hunt}'
*/
export const store = (args: { hunt: number | { id: number } } | [hunt: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/commentable/hunts/{hunt}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Commentable\HuntCommentController::store
* @see app/Http/Controllers/Commentable/HuntCommentController.php:26
* @route '/commentable/hunts/{hunt}'
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
* @see \App\Http\Controllers\Commentable\HuntCommentController::store
* @see app/Http/Controllers/Commentable/HuntCommentController.php:26
* @route '/commentable/hunts/{hunt}'
*/
store.post = (args: { hunt: number | { id: number } } | [hunt: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

const HuntCommentController = { store }

export default HuntCommentController