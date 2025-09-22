import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Commentable\HuntCommentController::comment
* @see app/Http/Controllers/Commentable/HuntCommentController.php:26
* @route '/commentable/hunts/{hunt}'
*/
export const comment = (args: { hunt: number | { id: number } } | [hunt: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: comment.url(args, options),
    method: 'post',
})

comment.definition = {
    methods: ["post"],
    url: '/commentable/hunts/{hunt}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Commentable\HuntCommentController::comment
* @see app/Http/Controllers/Commentable/HuntCommentController.php:26
* @route '/commentable/hunts/{hunt}'
*/
comment.url = (args: { hunt: number | { id: number } } | [hunt: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return comment.definition.url
            .replace('{hunt}', parsedArgs.hunt.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Commentable\HuntCommentController::comment
* @see app/Http/Controllers/Commentable/HuntCommentController.php:26
* @route '/commentable/hunts/{hunt}'
*/
comment.post = (args: { hunt: number | { id: number } } | [hunt: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: comment.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\HuntController::index
* @see app/Http/Controllers/HuntController.php:31
* @route '/hunts'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/hunts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\HuntController::index
* @see app/Http/Controllers/HuntController.php:31
* @route '/hunts'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\HuntController::index
* @see app/Http/Controllers/HuntController.php:31
* @route '/hunts'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\HuntController::index
* @see app/Http/Controllers/HuntController.php:31
* @route '/hunts'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\HuntController::store
* @see app/Http/Controllers/HuntController.php:51
* @route '/hunts'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/hunts',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\HuntController::store
* @see app/Http/Controllers/HuntController.php:51
* @route '/hunts'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\HuntController::store
* @see app/Http/Controllers/HuntController.php:51
* @route '/hunts'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\HuntController::destroy
* @see app/Http/Controllers/HuntController.php:63
* @route '/hunts/{hunt}'
*/
export const destroy = (args: { hunt: number | { id: number } } | [hunt: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/hunts/{hunt}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\HuntController::destroy
* @see app/Http/Controllers/HuntController.php:63
* @route '/hunts/{hunt}'
*/
destroy.url = (args: { hunt: number | { id: number } } | [hunt: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{hunt}', parsedArgs.hunt.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\HuntController::destroy
* @see app/Http/Controllers/HuntController.php:63
* @route '/hunts/{hunt}'
*/
destroy.delete = (args: { hunt: number | { id: number } } | [hunt: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Likeable\ToggleHuntLikeController::toggleLike
* @see app/Http/Controllers/Likeable/ToggleHuntLikeController.php:21
* @route '/likeable/{hunt}'
*/
export const toggleLike = (args: { hunt: number | { id: number } } | [hunt: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleLike.url(args, options),
    method: 'post',
})

toggleLike.definition = {
    methods: ["post"],
    url: '/likeable/{hunt}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Likeable\ToggleHuntLikeController::toggleLike
* @see app/Http/Controllers/Likeable/ToggleHuntLikeController.php:21
* @route '/likeable/{hunt}'
*/
toggleLike.url = (args: { hunt: number | { id: number } } | [hunt: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return toggleLike.definition.url
            .replace('{hunt}', parsedArgs.hunt.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Likeable\ToggleHuntLikeController::toggleLike
* @see app/Http/Controllers/Likeable/ToggleHuntLikeController.php:21
* @route '/likeable/{hunt}'
*/
toggleLike.post = (args: { hunt: number | { id: number } } | [hunt: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleLike.url(args, options),
    method: 'post',
})

const hunts = {
    comment: Object.assign(comment, comment),
    index: Object.assign(index, index),
    store: Object.assign(store, store),
    destroy: Object.assign(destroy, destroy),
    toggleLike: Object.assign(toggleLike, toggleLike),
}

export default hunts