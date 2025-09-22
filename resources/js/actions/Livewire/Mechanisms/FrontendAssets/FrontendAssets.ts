import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../../wayfinder'
/**
* @see \Livewire\Mechanisms\FrontendAssets\FrontendAssets::returnJavaScriptAsFile
* @see vendor/livewire/livewire/src/Mechanisms/FrontendAssets/FrontendAssets.php:78
* @route '/livewire/livewire.js'
*/
export const returnJavaScriptAsFile = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: returnJavaScriptAsFile.url(options),
    method: 'get',
})

returnJavaScriptAsFile.definition = {
    methods: ["get","head"],
    url: '/livewire/livewire.js',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Livewire\Mechanisms\FrontendAssets\FrontendAssets::returnJavaScriptAsFile
* @see vendor/livewire/livewire/src/Mechanisms/FrontendAssets/FrontendAssets.php:78
* @route '/livewire/livewire.js'
*/
returnJavaScriptAsFile.url = (options?: RouteQueryOptions) => {
    return returnJavaScriptAsFile.definition.url + queryParams(options)
}

/**
* @see \Livewire\Mechanisms\FrontendAssets\FrontendAssets::returnJavaScriptAsFile
* @see vendor/livewire/livewire/src/Mechanisms/FrontendAssets/FrontendAssets.php:78
* @route '/livewire/livewire.js'
*/
returnJavaScriptAsFile.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: returnJavaScriptAsFile.url(options),
    method: 'get',
})

/**
* @see \Livewire\Mechanisms\FrontendAssets\FrontendAssets::returnJavaScriptAsFile
* @see vendor/livewire/livewire/src/Mechanisms/FrontendAssets/FrontendAssets.php:78
* @route '/livewire/livewire.js'
*/
returnJavaScriptAsFile.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: returnJavaScriptAsFile.url(options),
    method: 'head',
})

/**
* @see \Livewire\Mechanisms\FrontendAssets\FrontendAssets::maps
* @see vendor/livewire/livewire/src/Mechanisms/FrontendAssets/FrontendAssets.php:87
* @route '/livewire/livewire.min.js.map'
*/
export const maps = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: maps.url(options),
    method: 'get',
})

maps.definition = {
    methods: ["get","head"],
    url: '/livewire/livewire.min.js.map',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Livewire\Mechanisms\FrontendAssets\FrontendAssets::maps
* @see vendor/livewire/livewire/src/Mechanisms/FrontendAssets/FrontendAssets.php:87
* @route '/livewire/livewire.min.js.map'
*/
maps.url = (options?: RouteQueryOptions) => {
    return maps.definition.url + queryParams(options)
}

/**
* @see \Livewire\Mechanisms\FrontendAssets\FrontendAssets::maps
* @see vendor/livewire/livewire/src/Mechanisms/FrontendAssets/FrontendAssets.php:87
* @route '/livewire/livewire.min.js.map'
*/
maps.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: maps.url(options),
    method: 'get',
})

/**
* @see \Livewire\Mechanisms\FrontendAssets\FrontendAssets::maps
* @see vendor/livewire/livewire/src/Mechanisms/FrontendAssets/FrontendAssets.php:87
* @route '/livewire/livewire.min.js.map'
*/
maps.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: maps.url(options),
    method: 'head',
})

const FrontendAssets = { returnJavaScriptAsFile, maps }

export default FrontendAssets