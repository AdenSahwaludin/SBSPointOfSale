import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
const index68b0973c1307ea9af137dc701b08d93f = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index68b0973c1307ea9af137dc701b08d93f.url(options),
    method: 'get',
})

index68b0973c1307ea9af137dc701b08d93f.definition = {
    methods: ["get","head"],
    url: '/admin/pelanggan',
} satisfies RouteDefinition<["get","head"]>

index68b0973c1307ea9af137dc701b08d93f.url = (options?: RouteQueryOptions) => {
    return index68b0973c1307ea9af137dc701b08d93f.definition.url + queryParams(options)
}

index68b0973c1307ea9af137dc701b08d93f.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index68b0973c1307ea9af137dc701b08d93f.url(options),
    method: 'get',
})
index68b0973c1307ea9af137dc701b08d93f.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index68b0973c1307ea9af137dc701b08d93f.url(options),
    method: 'head',
})

        const index68b0973c1307ea9af137dc701b08d93fForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index68b0973c1307ea9af137dc701b08d93f.url(options),
        method: 'get',
    })

                    index68b0973c1307ea9af137dc701b08d93fForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index68b0973c1307ea9af137dc701b08d93f.url(options),
            method: 'get',
        })
                    index68b0973c1307ea9af137dc701b08d93fForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index68b0973c1307ea9af137dc701b08d93f.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index68b0973c1307ea9af137dc701b08d93f.form = index68b0973c1307ea9af137dc701b08d93fForm
    const index1ce183231252f8529a508b652591d474 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index1ce183231252f8529a508b652591d474.url(options),
    method: 'get',
})

index1ce183231252f8529a508b652591d474.definition = {
    methods: ["get","head"],
    url: '/kasir/customers',
} satisfies RouteDefinition<["get","head"]>

index1ce183231252f8529a508b652591d474.url = (options?: RouteQueryOptions) => {
    return index1ce183231252f8529a508b652591d474.definition.url + queryParams(options)
}

index1ce183231252f8529a508b652591d474.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index1ce183231252f8529a508b652591d474.url(options),
    method: 'get',
})
index1ce183231252f8529a508b652591d474.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index1ce183231252f8529a508b652591d474.url(options),
    method: 'head',
})

        const index1ce183231252f8529a508b652591d474Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index1ce183231252f8529a508b652591d474.url(options),
        method: 'get',
    })

                    index1ce183231252f8529a508b652591d474Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index1ce183231252f8529a508b652591d474.url(options),
            method: 'get',
        })
                    index1ce183231252f8529a508b652591d474Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index1ce183231252f8529a508b652591d474.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index1ce183231252f8529a508b652591d474.form = index1ce183231252f8529a508b652591d474Form

export const index = {
    '/admin/pelanggan': index68b0973c1307ea9af137dc701b08d93f,
    '/kasir/customers': index1ce183231252f8529a508b652591d474,
}

const createea6f8ffefd5e11d5f17a2a8b46278f24 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: createea6f8ffefd5e11d5f17a2a8b46278f24.url(options),
    method: 'get',
})

createea6f8ffefd5e11d5f17a2a8b46278f24.definition = {
    methods: ["get","head"],
    url: '/admin/pelanggan/create',
} satisfies RouteDefinition<["get","head"]>

createea6f8ffefd5e11d5f17a2a8b46278f24.url = (options?: RouteQueryOptions) => {
    return createea6f8ffefd5e11d5f17a2a8b46278f24.definition.url + queryParams(options)
}

createea6f8ffefd5e11d5f17a2a8b46278f24.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: createea6f8ffefd5e11d5f17a2a8b46278f24.url(options),
    method: 'get',
})
createea6f8ffefd5e11d5f17a2a8b46278f24.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: createea6f8ffefd5e11d5f17a2a8b46278f24.url(options),
    method: 'head',
})

        const createea6f8ffefd5e11d5f17a2a8b46278f24Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: createea6f8ffefd5e11d5f17a2a8b46278f24.url(options),
        method: 'get',
    })

                    createea6f8ffefd5e11d5f17a2a8b46278f24Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: createea6f8ffefd5e11d5f17a2a8b46278f24.url(options),
            method: 'get',
        })
                    createea6f8ffefd5e11d5f17a2a8b46278f24Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: createea6f8ffefd5e11d5f17a2a8b46278f24.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    createea6f8ffefd5e11d5f17a2a8b46278f24.form = createea6f8ffefd5e11d5f17a2a8b46278f24Form
    const createe71a7daf8a7383fcf48e65e0e26fd3ef = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: createe71a7daf8a7383fcf48e65e0e26fd3ef.url(options),
    method: 'get',
})

createe71a7daf8a7383fcf48e65e0e26fd3ef.definition = {
    methods: ["get","head"],
    url: '/kasir/customers/create',
} satisfies RouteDefinition<["get","head"]>

createe71a7daf8a7383fcf48e65e0e26fd3ef.url = (options?: RouteQueryOptions) => {
    return createe71a7daf8a7383fcf48e65e0e26fd3ef.definition.url + queryParams(options)
}

createe71a7daf8a7383fcf48e65e0e26fd3ef.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: createe71a7daf8a7383fcf48e65e0e26fd3ef.url(options),
    method: 'get',
})
createe71a7daf8a7383fcf48e65e0e26fd3ef.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: createe71a7daf8a7383fcf48e65e0e26fd3ef.url(options),
    method: 'head',
})

        const createe71a7daf8a7383fcf48e65e0e26fd3efForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: createe71a7daf8a7383fcf48e65e0e26fd3ef.url(options),
        method: 'get',
    })

                    createe71a7daf8a7383fcf48e65e0e26fd3efForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: createe71a7daf8a7383fcf48e65e0e26fd3ef.url(options),
            method: 'get',
        })
                    createe71a7daf8a7383fcf48e65e0e26fd3efForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: createe71a7daf8a7383fcf48e65e0e26fd3ef.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    createe71a7daf8a7383fcf48e65e0e26fd3ef.form = createe71a7daf8a7383fcf48e65e0e26fd3efForm

export const create = {
    '/admin/pelanggan/create': createea6f8ffefd5e11d5f17a2a8b46278f24,
    '/kasir/customers/create': createe71a7daf8a7383fcf48e65e0e26fd3ef,
}

const store68b0973c1307ea9af137dc701b08d93f = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store68b0973c1307ea9af137dc701b08d93f.url(options),
    method: 'post',
})

store68b0973c1307ea9af137dc701b08d93f.definition = {
    methods: ["post"],
    url: '/admin/pelanggan',
} satisfies RouteDefinition<["post"]>

store68b0973c1307ea9af137dc701b08d93f.url = (options?: RouteQueryOptions) => {
    return store68b0973c1307ea9af137dc701b08d93f.definition.url + queryParams(options)
}

store68b0973c1307ea9af137dc701b08d93f.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store68b0973c1307ea9af137dc701b08d93f.url(options),
    method: 'post',
})

        const store68b0973c1307ea9af137dc701b08d93fForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store68b0973c1307ea9af137dc701b08d93f.url(options),
        method: 'post',
    })

                    store68b0973c1307ea9af137dc701b08d93fForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store68b0973c1307ea9af137dc701b08d93f.url(options),
            method: 'post',
        })
    
    store68b0973c1307ea9af137dc701b08d93f.form = store68b0973c1307ea9af137dc701b08d93fForm
    const store1ce183231252f8529a508b652591d474 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store1ce183231252f8529a508b652591d474.url(options),
    method: 'post',
})

store1ce183231252f8529a508b652591d474.definition = {
    methods: ["post"],
    url: '/kasir/customers',
} satisfies RouteDefinition<["post"]>

store1ce183231252f8529a508b652591d474.url = (options?: RouteQueryOptions) => {
    return store1ce183231252f8529a508b652591d474.definition.url + queryParams(options)
}

store1ce183231252f8529a508b652591d474.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store1ce183231252f8529a508b652591d474.url(options),
    method: 'post',
})

        const store1ce183231252f8529a508b652591d474Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store1ce183231252f8529a508b652591d474.url(options),
        method: 'post',
    })

                    store1ce183231252f8529a508b652591d474Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store1ce183231252f8529a508b652591d474.url(options),
            method: 'post',
        })
    
    store1ce183231252f8529a508b652591d474.form = store1ce183231252f8529a508b652591d474Form

export const store = {
    '/admin/pelanggan': store68b0973c1307ea9af137dc701b08d93f,
    '/kasir/customers': store1ce183231252f8529a508b652591d474,
}

const show86bd4d75336de4a9f00b96db1066af5d = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show86bd4d75336de4a9f00b96db1066af5d.url(args, options),
    method: 'get',
})

show86bd4d75336de4a9f00b96db1066af5d.definition = {
    methods: ["get","head"],
    url: '/admin/pelanggan/{pelanggan}',
} satisfies RouteDefinition<["get","head"]>

show86bd4d75336de4a9f00b96db1066af5d.url = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { pelanggan: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    pelanggan: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        pelanggan: args.pelanggan,
                }

    return show86bd4d75336de4a9f00b96db1066af5d.definition.url
            .replace('{pelanggan}', parsedArgs.pelanggan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

show86bd4d75336de4a9f00b96db1066af5d.get = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show86bd4d75336de4a9f00b96db1066af5d.url(args, options),
    method: 'get',
})
show86bd4d75336de4a9f00b96db1066af5d.head = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show86bd4d75336de4a9f00b96db1066af5d.url(args, options),
    method: 'head',
})

        const show86bd4d75336de4a9f00b96db1066af5dForm = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show86bd4d75336de4a9f00b96db1066af5d.url(args, options),
        method: 'get',
    })

                    show86bd4d75336de4a9f00b96db1066af5dForm.get = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show86bd4d75336de4a9f00b96db1066af5d.url(args, options),
            method: 'get',
        })
                    show86bd4d75336de4a9f00b96db1066af5dForm.head = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show86bd4d75336de4a9f00b96db1066af5d.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show86bd4d75336de4a9f00b96db1066af5d.form = show86bd4d75336de4a9f00b96db1066af5dForm
    const show3a06cbd6724a6925a9ffb9bb6e6d8fda = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show3a06cbd6724a6925a9ffb9bb6e6d8fda.url(args, options),
    method: 'get',
})

show3a06cbd6724a6925a9ffb9bb6e6d8fda.definition = {
    methods: ["get","head"],
    url: '/kasir/customers/{id}',
} satisfies RouteDefinition<["get","head"]>

show3a06cbd6724a6925a9ffb9bb6e6d8fda.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return show3a06cbd6724a6925a9ffb9bb6e6d8fda.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

show3a06cbd6724a6925a9ffb9bb6e6d8fda.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show3a06cbd6724a6925a9ffb9bb6e6d8fda.url(args, options),
    method: 'get',
})
show3a06cbd6724a6925a9ffb9bb6e6d8fda.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show3a06cbd6724a6925a9ffb9bb6e6d8fda.url(args, options),
    method: 'head',
})

        const show3a06cbd6724a6925a9ffb9bb6e6d8fdaForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show3a06cbd6724a6925a9ffb9bb6e6d8fda.url(args, options),
        method: 'get',
    })

                    show3a06cbd6724a6925a9ffb9bb6e6d8fdaForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show3a06cbd6724a6925a9ffb9bb6e6d8fda.url(args, options),
            method: 'get',
        })
                    show3a06cbd6724a6925a9ffb9bb6e6d8fdaForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show3a06cbd6724a6925a9ffb9bb6e6d8fda.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show3a06cbd6724a6925a9ffb9bb6e6d8fda.form = show3a06cbd6724a6925a9ffb9bb6e6d8fdaForm

export const show = {
    '/admin/pelanggan/{pelanggan}': show86bd4d75336de4a9f00b96db1066af5d,
    '/kasir/customers/{id}': show3a06cbd6724a6925a9ffb9bb6e6d8fda,
}

const edite4899c6c5e512e2a423017b028a9b33b = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edite4899c6c5e512e2a423017b028a9b33b.url(args, options),
    method: 'get',
})

edite4899c6c5e512e2a423017b028a9b33b.definition = {
    methods: ["get","head"],
    url: '/admin/pelanggan/{pelanggan}/edit',
} satisfies RouteDefinition<["get","head"]>

edite4899c6c5e512e2a423017b028a9b33b.url = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { pelanggan: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    pelanggan: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        pelanggan: args.pelanggan,
                }

    return edite4899c6c5e512e2a423017b028a9b33b.definition.url
            .replace('{pelanggan}', parsedArgs.pelanggan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

edite4899c6c5e512e2a423017b028a9b33b.get = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edite4899c6c5e512e2a423017b028a9b33b.url(args, options),
    method: 'get',
})
edite4899c6c5e512e2a423017b028a9b33b.head = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edite4899c6c5e512e2a423017b028a9b33b.url(args, options),
    method: 'head',
})

        const edite4899c6c5e512e2a423017b028a9b33bForm = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edite4899c6c5e512e2a423017b028a9b33b.url(args, options),
        method: 'get',
    })

                    edite4899c6c5e512e2a423017b028a9b33bForm.get = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edite4899c6c5e512e2a423017b028a9b33b.url(args, options),
            method: 'get',
        })
                    edite4899c6c5e512e2a423017b028a9b33bForm.head = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edite4899c6c5e512e2a423017b028a9b33b.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edite4899c6c5e512e2a423017b028a9b33b.form = edite4899c6c5e512e2a423017b028a9b33bForm
    const edit1ac499b57e0a54405ef73b614d31f272 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit1ac499b57e0a54405ef73b614d31f272.url(args, options),
    method: 'get',
})

edit1ac499b57e0a54405ef73b614d31f272.definition = {
    methods: ["get","head"],
    url: '/kasir/customers/{id}/edit',
} satisfies RouteDefinition<["get","head"]>

edit1ac499b57e0a54405ef73b614d31f272.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return edit1ac499b57e0a54405ef73b614d31f272.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

edit1ac499b57e0a54405ef73b614d31f272.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit1ac499b57e0a54405ef73b614d31f272.url(args, options),
    method: 'get',
})
edit1ac499b57e0a54405ef73b614d31f272.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit1ac499b57e0a54405ef73b614d31f272.url(args, options),
    method: 'head',
})

        const edit1ac499b57e0a54405ef73b614d31f272Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit1ac499b57e0a54405ef73b614d31f272.url(args, options),
        method: 'get',
    })

                    edit1ac499b57e0a54405ef73b614d31f272Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit1ac499b57e0a54405ef73b614d31f272.url(args, options),
            method: 'get',
        })
                    edit1ac499b57e0a54405ef73b614d31f272Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit1ac499b57e0a54405ef73b614d31f272.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edit1ac499b57e0a54405ef73b614d31f272.form = edit1ac499b57e0a54405ef73b614d31f272Form

export const edit = {
    '/admin/pelanggan/{pelanggan}/edit': edite4899c6c5e512e2a423017b028a9b33b,
    '/kasir/customers/{id}/edit': edit1ac499b57e0a54405ef73b614d31f272,
}

const update86bd4d75336de4a9f00b96db1066af5d = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update86bd4d75336de4a9f00b96db1066af5d.url(args, options),
    method: 'put',
})

update86bd4d75336de4a9f00b96db1066af5d.definition = {
    methods: ["put","patch"],
    url: '/admin/pelanggan/{pelanggan}',
} satisfies RouteDefinition<["put","patch"]>

update86bd4d75336de4a9f00b96db1066af5d.url = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { pelanggan: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    pelanggan: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        pelanggan: args.pelanggan,
                }

    return update86bd4d75336de4a9f00b96db1066af5d.definition.url
            .replace('{pelanggan}', parsedArgs.pelanggan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

update86bd4d75336de4a9f00b96db1066af5d.put = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update86bd4d75336de4a9f00b96db1066af5d.url(args, options),
    method: 'put',
})
update86bd4d75336de4a9f00b96db1066af5d.patch = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update86bd4d75336de4a9f00b96db1066af5d.url(args, options),
    method: 'patch',
})

        const update86bd4d75336de4a9f00b96db1066af5dForm = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update86bd4d75336de4a9f00b96db1066af5d.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

                    update86bd4d75336de4a9f00b96db1066af5dForm.put = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update86bd4d75336de4a9f00b96db1066af5d.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
                    update86bd4d75336de4a9f00b96db1066af5dForm.patch = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update86bd4d75336de4a9f00b96db1066af5d.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update86bd4d75336de4a9f00b96db1066af5d.form = update86bd4d75336de4a9f00b96db1066af5dForm
    const update3a06cbd6724a6925a9ffb9bb6e6d8fda = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update3a06cbd6724a6925a9ffb9bb6e6d8fda.url(args, options),
    method: 'patch',
})

update3a06cbd6724a6925a9ffb9bb6e6d8fda.definition = {
    methods: ["patch"],
    url: '/kasir/customers/{id}',
} satisfies RouteDefinition<["patch"]>

update3a06cbd6724a6925a9ffb9bb6e6d8fda.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return update3a06cbd6724a6925a9ffb9bb6e6d8fda.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

update3a06cbd6724a6925a9ffb9bb6e6d8fda.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update3a06cbd6724a6925a9ffb9bb6e6d8fda.url(args, options),
    method: 'patch',
})

        const update3a06cbd6724a6925a9ffb9bb6e6d8fdaForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update3a06cbd6724a6925a9ffb9bb6e6d8fda.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

                    update3a06cbd6724a6925a9ffb9bb6e6d8fdaForm.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update3a06cbd6724a6925a9ffb9bb6e6d8fda.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update3a06cbd6724a6925a9ffb9bb6e6d8fda.form = update3a06cbd6724a6925a9ffb9bb6e6d8fdaForm

export const update = {
    '/admin/pelanggan/{pelanggan}': update86bd4d75336de4a9f00b96db1066af5d,
    '/kasir/customers/{id}': update3a06cbd6724a6925a9ffb9bb6e6d8fda,
}

const destroy86bd4d75336de4a9f00b96db1066af5d = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy86bd4d75336de4a9f00b96db1066af5d.url(args, options),
    method: 'delete',
})

destroy86bd4d75336de4a9f00b96db1066af5d.definition = {
    methods: ["delete"],
    url: '/admin/pelanggan/{pelanggan}',
} satisfies RouteDefinition<["delete"]>

destroy86bd4d75336de4a9f00b96db1066af5d.url = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { pelanggan: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    pelanggan: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        pelanggan: args.pelanggan,
                }

    return destroy86bd4d75336de4a9f00b96db1066af5d.definition.url
            .replace('{pelanggan}', parsedArgs.pelanggan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

destroy86bd4d75336de4a9f00b96db1066af5d.delete = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy86bd4d75336de4a9f00b96db1066af5d.url(args, options),
    method: 'delete',
})

        const destroy86bd4d75336de4a9f00b96db1066af5dForm = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy86bd4d75336de4a9f00b96db1066af5d.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

                    destroy86bd4d75336de4a9f00b96db1066af5dForm.delete = (args: { pelanggan: string | number } | [pelanggan: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy86bd4d75336de4a9f00b96db1066af5d.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy86bd4d75336de4a9f00b96db1066af5d.form = destroy86bd4d75336de4a9f00b96db1066af5dForm
    const destroy3a06cbd6724a6925a9ffb9bb6e6d8fda = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy3a06cbd6724a6925a9ffb9bb6e6d8fda.url(args, options),
    method: 'delete',
})

destroy3a06cbd6724a6925a9ffb9bb6e6d8fda.definition = {
    methods: ["delete"],
    url: '/kasir/customers/{id}',
} satisfies RouteDefinition<["delete"]>

destroy3a06cbd6724a6925a9ffb9bb6e6d8fda.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return destroy3a06cbd6724a6925a9ffb9bb6e6d8fda.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

destroy3a06cbd6724a6925a9ffb9bb6e6d8fda.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy3a06cbd6724a6925a9ffb9bb6e6d8fda.url(args, options),
    method: 'delete',
})

        const destroy3a06cbd6724a6925a9ffb9bb6e6d8fdaForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy3a06cbd6724a6925a9ffb9bb6e6d8fda.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

                    destroy3a06cbd6724a6925a9ffb9bb6e6d8fdaForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy3a06cbd6724a6925a9ffb9bb6e6d8fda.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy3a06cbd6724a6925a9ffb9bb6e6d8fda.form = destroy3a06cbd6724a6925a9ffb9bb6e6d8fdaForm

export const destroy = {
    '/admin/pelanggan/{pelanggan}': destroy86bd4d75336de4a9f00b96db1066af5d,
    '/kasir/customers/{id}': destroy3a06cbd6724a6925a9ffb9bb6e6d8fda,
}

const PelangganController = { index, create, store, show, edit, update, destroy }

export default PelangganController