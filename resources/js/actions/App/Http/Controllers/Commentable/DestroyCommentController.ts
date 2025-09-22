import { applyUrlDefaults, queryParams, type RouteDefinition, type RouteQueryOptions } from './../../../../../wayfinder';
/**
 * @see \App\Http\Controllers\Commentable\DestroyCommentController::destroy
 * @see app/Http/Controllers/Commentable/DestroyCommentController.php:26
 * @route '/commentable/comments/{comment}'
 */
export const destroy = (
    args: { comment: number | { id: number } } | [comment: number | { id: number }] | number | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
});

destroy.definition = {
    methods: ['delete'],
    url: '/commentable/comments/{comment}',
} satisfies RouteDefinition<['delete']>;

/**
 * @see \App\Http\Controllers\Commentable\DestroyCommentController::destroy
 * @see app/Http/Controllers/Commentable/DestroyCommentController.php:26
 * @route '/commentable/comments/{comment}'
 */
destroy.url = (
    args: { comment: number | { id: number } } | [comment: number | { id: number }] | number | { id: number },
    options?: RouteQueryOptions,
) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { comment: args };
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { comment: args.id };
    }

    if (Array.isArray(args)) {
        args = {
            comment: args[0],
        };
    }

    args = applyUrlDefaults(args);

    const parsedArgs = {
        comment: typeof args.comment === 'object' ? args.comment.id : args.comment,
    };

    return destroy.definition.url.replace('{comment}', parsedArgs.comment.toString()).replace(/\/+$/, '') + queryParams(options);
};

/**
 * @see \App\Http\Controllers\Commentable\DestroyCommentController::destroy
 * @see app/Http/Controllers/Commentable/DestroyCommentController.php:26
 * @route '/commentable/comments/{comment}'
 */
destroy.delete = (
    args: { comment: number | { id: number } } | [comment: number | { id: number }] | number | { id: number },
    options?: RouteQueryOptions,
): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
});

const DestroyCommentController = { destroy };

export default DestroyCommentController;
