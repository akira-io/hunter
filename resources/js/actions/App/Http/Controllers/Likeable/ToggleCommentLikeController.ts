import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Likeable\ToggleCommentLikeController::store
* @see app/Http/Controllers/Likeable/ToggleCommentLikeController.php:21
* @route '/likeable/comments/{comment}'
*/
export const store = (args: { comment: number | { id: number } } | [comment: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/likeable/comments/{comment}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Likeable\ToggleCommentLikeController::store
* @see app/Http/Controllers/Likeable/ToggleCommentLikeController.php:21
* @route '/likeable/comments/{comment}'
*/
store.url = (args: { comment: number | { id: number } } | [comment: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { comment: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { comment: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            comment: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        comment: typeof args.comment === 'object'
        ? args.comment.id
        : args.comment,
    }

    return store.definition.url
            .replace('{comment}', parsedArgs.comment.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Likeable\ToggleCommentLikeController::store
* @see app/Http/Controllers/Likeable/ToggleCommentLikeController.php:21
* @route '/likeable/comments/{comment}'
*/
store.post = (args: { comment: number | { id: number } } | [comment: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

const ToggleCommentLikeController = { store }

export default ToggleCommentLikeController