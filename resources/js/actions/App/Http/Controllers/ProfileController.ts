import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
const showd0843ae791788ddea41893f8850531f6 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showd0843ae791788ddea41893f8850531f6.url(options),
    method: 'get',
})

showd0843ae791788ddea41893f8850531f6.definition = {
    methods: ["get","head"],
    url: '/auth/profile',
} satisfies RouteDefinition<["get","head"]>

showd0843ae791788ddea41893f8850531f6.url = (options?: RouteQueryOptions) => {
    return showd0843ae791788ddea41893f8850531f6.definition.url + queryParams(options)
}

showd0843ae791788ddea41893f8850531f6.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showd0843ae791788ddea41893f8850531f6.url(options),
    method: 'get',
})
showd0843ae791788ddea41893f8850531f6.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: showd0843ae791788ddea41893f8850531f6.url(options),
    method: 'head',
})

        const showd0843ae791788ddea41893f8850531f6Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: showd0843ae791788ddea41893f8850531f6.url(options),
        method: 'get',
    })

                    showd0843ae791788ddea41893f8850531f6Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showd0843ae791788ddea41893f8850531f6.url(options),
            method: 'get',
        })
                    showd0843ae791788ddea41893f8850531f6Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showd0843ae791788ddea41893f8850531f6.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    showd0843ae791788ddea41893f8850531f6.form = showd0843ae791788ddea41893f8850531f6Form
    const showeec3436163a255180d15377a11617ad4 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showeec3436163a255180d15377a11617ad4.url(options),
    method: 'get',
})

showeec3436163a255180d15377a11617ad4.definition = {
    methods: ["get","head"],
    url: '/kasir/profile',
} satisfies RouteDefinition<["get","head"]>

showeec3436163a255180d15377a11617ad4.url = (options?: RouteQueryOptions) => {
    return showeec3436163a255180d15377a11617ad4.definition.url + queryParams(options)
}

showeec3436163a255180d15377a11617ad4.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showeec3436163a255180d15377a11617ad4.url(options),
    method: 'get',
})
showeec3436163a255180d15377a11617ad4.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: showeec3436163a255180d15377a11617ad4.url(options),
    method: 'head',
})

        const showeec3436163a255180d15377a11617ad4Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: showeec3436163a255180d15377a11617ad4.url(options),
        method: 'get',
    })

                    showeec3436163a255180d15377a11617ad4Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showeec3436163a255180d15377a11617ad4.url(options),
            method: 'get',
        })
                    showeec3436163a255180d15377a11617ad4Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showeec3436163a255180d15377a11617ad4.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    showeec3436163a255180d15377a11617ad4.form = showeec3436163a255180d15377a11617ad4Form

export const show = {
    '/auth/profile': showd0843ae791788ddea41893f8850531f6,
    '/kasir/profile': showeec3436163a255180d15377a11617ad4,
}

const updated0843ae791788ddea41893f8850531f6 = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updated0843ae791788ddea41893f8850531f6.url(options),
    method: 'patch',
})

updated0843ae791788ddea41893f8850531f6.definition = {
    methods: ["patch"],
    url: '/auth/profile',
} satisfies RouteDefinition<["patch"]>

updated0843ae791788ddea41893f8850531f6.url = (options?: RouteQueryOptions) => {
    return updated0843ae791788ddea41893f8850531f6.definition.url + queryParams(options)
}

updated0843ae791788ddea41893f8850531f6.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updated0843ae791788ddea41893f8850531f6.url(options),
    method: 'patch',
})

        const updated0843ae791788ddea41893f8850531f6Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updated0843ae791788ddea41893f8850531f6.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

                    updated0843ae791788ddea41893f8850531f6Form.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updated0843ae791788ddea41893f8850531f6.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updated0843ae791788ddea41893f8850531f6.form = updated0843ae791788ddea41893f8850531f6Form
    const updateeec3436163a255180d15377a11617ad4 = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateeec3436163a255180d15377a11617ad4.url(options),
    method: 'patch',
})

updateeec3436163a255180d15377a11617ad4.definition = {
    methods: ["patch"],
    url: '/kasir/profile',
} satisfies RouteDefinition<["patch"]>

updateeec3436163a255180d15377a11617ad4.url = (options?: RouteQueryOptions) => {
    return updateeec3436163a255180d15377a11617ad4.definition.url + queryParams(options)
}

updateeec3436163a255180d15377a11617ad4.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateeec3436163a255180d15377a11617ad4.url(options),
    method: 'patch',
})

        const updateeec3436163a255180d15377a11617ad4Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateeec3436163a255180d15377a11617ad4.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

                    updateeec3436163a255180d15377a11617ad4Form.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateeec3436163a255180d15377a11617ad4.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateeec3436163a255180d15377a11617ad4.form = updateeec3436163a255180d15377a11617ad4Form

export const update = {
    '/auth/profile': updated0843ae791788ddea41893f8850531f6,
    '/kasir/profile': updateeec3436163a255180d15377a11617ad4,
}

const updatePasswordd79972dca9eb5e28cf641b39425fab00 = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updatePasswordd79972dca9eb5e28cf641b39425fab00.url(options),
    method: 'patch',
})

updatePasswordd79972dca9eb5e28cf641b39425fab00.definition = {
    methods: ["patch"],
    url: '/auth/profile/password',
} satisfies RouteDefinition<["patch"]>

updatePasswordd79972dca9eb5e28cf641b39425fab00.url = (options?: RouteQueryOptions) => {
    return updatePasswordd79972dca9eb5e28cf641b39425fab00.definition.url + queryParams(options)
}

updatePasswordd79972dca9eb5e28cf641b39425fab00.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updatePasswordd79972dca9eb5e28cf641b39425fab00.url(options),
    method: 'patch',
})

        const updatePasswordd79972dca9eb5e28cf641b39425fab00Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updatePasswordd79972dca9eb5e28cf641b39425fab00.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

                    updatePasswordd79972dca9eb5e28cf641b39425fab00Form.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updatePasswordd79972dca9eb5e28cf641b39425fab00.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updatePasswordd79972dca9eb5e28cf641b39425fab00.form = updatePasswordd79972dca9eb5e28cf641b39425fab00Form
    const updatePassword51b75a5981b36358c6ad3ff214354c5c = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updatePassword51b75a5981b36358c6ad3ff214354c5c.url(options),
    method: 'patch',
})

updatePassword51b75a5981b36358c6ad3ff214354c5c.definition = {
    methods: ["patch"],
    url: '/kasir/profile/password',
} satisfies RouteDefinition<["patch"]>

updatePassword51b75a5981b36358c6ad3ff214354c5c.url = (options?: RouteQueryOptions) => {
    return updatePassword51b75a5981b36358c6ad3ff214354c5c.definition.url + queryParams(options)
}

updatePassword51b75a5981b36358c6ad3ff214354c5c.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updatePassword51b75a5981b36358c6ad3ff214354c5c.url(options),
    method: 'patch',
})

        const updatePassword51b75a5981b36358c6ad3ff214354c5cForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updatePassword51b75a5981b36358c6ad3ff214354c5c.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

                    updatePassword51b75a5981b36358c6ad3ff214354c5cForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updatePassword51b75a5981b36358c6ad3ff214354c5c.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updatePassword51b75a5981b36358c6ad3ff214354c5c.form = updatePassword51b75a5981b36358c6ad3ff214354c5cForm

export const updatePassword = {
    '/auth/profile/password': updatePasswordd79972dca9eb5e28cf641b39425fab00,
    '/kasir/profile/password': updatePassword51b75a5981b36358c6ad3ff214354c5c,
}

const ProfileController = { show, update, updatePassword }

export default ProfileController