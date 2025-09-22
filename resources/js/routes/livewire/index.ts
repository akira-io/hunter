import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \Livewire\Mechanisms\HandleRequests\HandleRequests::update
* @see vendor/livewire/livewire/src/Mechanisms/HandleRequests/HandleRequests.php:79
* @route '/livewire/update'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/livewire/update',
} satisfies RouteDefinition<["post"]>

/**
* @see \Livewire\Mechanisms\HandleRequests\HandleRequests::update
* @see vendor/livewire/livewire/src/Mechanisms/HandleRequests/HandleRequests.php:79
* @route '/livewire/update'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \Livewire\Mechanisms\HandleRequests\HandleRequests::update
* @see vendor/livewire/livewire/src/Mechanisms/HandleRequests/HandleRequests.php:79
* @route '/livewire/update'
*/
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

/**
* @see \Livewire\Features\SupportFileUploads\FileUploadController::uploadFile
* @see vendor/livewire/livewire/src/Features/SupportFileUploads/FileUploadController.php:22
* @route '/livewire/upload-file'
*/
export const uploadFile = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadFile.url(options),
    method: 'post',
})

uploadFile.definition = {
    methods: ["post"],
    url: '/livewire/upload-file',
} satisfies RouteDefinition<["post"]>

/**
* @see \Livewire\Features\SupportFileUploads\FileUploadController::uploadFile
* @see vendor/livewire/livewire/src/Features/SupportFileUploads/FileUploadController.php:22
* @route '/livewire/upload-file'
*/
uploadFile.url = (options?: RouteQueryOptions) => {
    return uploadFile.definition.url + queryParams(options)
}

/**
* @see \Livewire\Features\SupportFileUploads\FileUploadController::uploadFile
* @see vendor/livewire/livewire/src/Features/SupportFileUploads/FileUploadController.php:22
* @route '/livewire/upload-file'
*/
uploadFile.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadFile.url(options),
    method: 'post',
})

/**
* @see \Livewire\Features\SupportFileUploads\FilePreviewController::previewFile
* @see vendor/livewire/livewire/src/Features/SupportFileUploads/FilePreviewController.php:18
* @route '/livewire/preview-file/{filename}'
*/
export const previewFile = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewFile.url(args, options),
    method: 'get',
})

previewFile.definition = {
    methods: ["get","head"],
    url: '/livewire/preview-file/{filename}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Livewire\Features\SupportFileUploads\FilePreviewController::previewFile
* @see vendor/livewire/livewire/src/Features/SupportFileUploads/FilePreviewController.php:18
* @route '/livewire/preview-file/{filename}'
*/
previewFile.url = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { filename: args }
    }

    if (Array.isArray(args)) {
        args = {
            filename: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        filename: args.filename,
    }

    return previewFile.definition.url
            .replace('{filename}', parsedArgs.filename.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Livewire\Features\SupportFileUploads\FilePreviewController::previewFile
* @see vendor/livewire/livewire/src/Features/SupportFileUploads/FilePreviewController.php:18
* @route '/livewire/preview-file/{filename}'
*/
previewFile.get = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: previewFile.url(args, options),
    method: 'get',
})

/**
* @see \Livewire\Features\SupportFileUploads\FilePreviewController::previewFile
* @see vendor/livewire/livewire/src/Features/SupportFileUploads/FilePreviewController.php:18
* @route '/livewire/preview-file/{filename}'
*/
previewFile.head = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: previewFile.url(args, options),
    method: 'head',
})

const livewire = {
    update: Object.assign(update, update),
    uploadFile: Object.assign(uploadFile, uploadFile),
    previewFile: Object.assign(previewFile, previewFile),
}

export default livewire