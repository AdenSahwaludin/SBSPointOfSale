import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
import items from './items'
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/kasir/goods-in',
} satisfies RouteDefinition<["get","head"]>

index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

        const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

                    indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
                    indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/kasir/goods-in/create',
} satisfies RouteDefinition<["get","head"]>

create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

        const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

                    createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
                    createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create.form = createForm
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/kasir/goods-in',
} satisfies RouteDefinition<["post"]>

store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

        const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

                    storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
export const show = (args: { goods_in: string | number } | [goods_in: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/kasir/goods-in/{goods_in}',
} satisfies RouteDefinition<["get","head"]>

show.url = (args: { goods_in: string | number } | [goods_in: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { goods_in: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    goods_in: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        goods_in: args.goods_in,
                }

    return show.definition.url
            .replace('{goods_in}', parsedArgs.goods_in.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

show.get = (args: { goods_in: string | number } | [goods_in: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
show.head = (args: { goods_in: string | number } | [goods_in: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

        const showForm = (args: { goods_in: string | number } | [goods_in: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

                    showForm.get = (args: { goods_in: string | number } | [goods_in: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
                    showForm.head = (args: { goods_in: string | number } | [goods_in: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
export const submit = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submit.url(args, options),
    method: 'post',
})

submit.definition = {
    methods: ["post"],
    url: '/kasir/goods-in/{goodsIn}/submit',
} satisfies RouteDefinition<["post"]>

submit.url = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { goodsIn: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id_pemesanan_barang' in args) {
            args = { goodsIn: args.id_pemesanan_barang }
        }
    
    if (Array.isArray(args)) {
        args = {
                    goodsIn: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        goodsIn: typeof args.goodsIn === 'object'
                ? args.goodsIn.id_pemesanan_barang
                : args.goodsIn,
                }

    return submit.definition.url
            .replace('{goodsIn}', parsedArgs.goodsIn.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

submit.post = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: submit.url(args, options),
    method: 'post',
})

        const submitForm = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: submit.url(args, options),
        method: 'post',
    })

                    submitForm.post = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: submit.url(args, options),
            method: 'post',
        })
    
    submit.form = submitForm
export const destroy = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/kasir/goods-in/{goodsIn}',
} satisfies RouteDefinition<["delete"]>

destroy.url = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { goodsIn: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id_pemesanan_barang' in args) {
            args = { goodsIn: args.id_pemesanan_barang }
        }
    
    if (Array.isArray(args)) {
        args = {
                    goodsIn: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        goodsIn: typeof args.goodsIn === 'object'
                ? args.goodsIn.id_pemesanan_barang
                : args.goodsIn,
                }

    return destroy.definition.url
            .replace('{goodsIn}', parsedArgs.goodsIn.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

destroy.delete = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

        const destroyForm = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

                    destroyForm.delete = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
export const receivingShow = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: receivingShow.url(args, options),
    method: 'get',
})

receivingShow.definition = {
    methods: ["get","head"],
    url: '/kasir/goods-in/{goodsIn}/receiving',
} satisfies RouteDefinition<["get","head"]>

receivingShow.url = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { goodsIn: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id_pemesanan_barang' in args) {
            args = { goodsIn: args.id_pemesanan_barang }
        }
    
    if (Array.isArray(args)) {
        args = {
                    goodsIn: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        goodsIn: typeof args.goodsIn === 'object'
                ? args.goodsIn.id_pemesanan_barang
                : args.goodsIn,
                }

    return receivingShow.definition.url
            .replace('{goodsIn}', parsedArgs.goodsIn.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

receivingShow.get = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: receivingShow.url(args, options),
    method: 'get',
})
receivingShow.head = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: receivingShow.url(args, options),
    method: 'head',
})

        const receivingShowForm = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: receivingShow.url(args, options),
        method: 'get',
    })

                    receivingShowForm.get = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: receivingShow.url(args, options),
            method: 'get',
        })
                    receivingShowForm.head = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: receivingShow.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    receivingShow.form = receivingShowForm
export const recordReceived = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: recordReceived.url(args, options),
    method: 'post',
})

recordReceived.definition = {
    methods: ["post"],
    url: '/kasir/goods-in/{goodsIn}/record-received',
} satisfies RouteDefinition<["post"]>

recordReceived.url = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { goodsIn: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id_pemesanan_barang' in args) {
            args = { goodsIn: args.id_pemesanan_barang }
        }
    
    if (Array.isArray(args)) {
        args = {
                    goodsIn: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        goodsIn: typeof args.goodsIn === 'object'
                ? args.goodsIn.id_pemesanan_barang
                : args.goodsIn,
                }

    return recordReceived.definition.url
            .replace('{goodsIn}', parsedArgs.goodsIn.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

recordReceived.post = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: recordReceived.url(args, options),
    method: 'post',
})

        const recordReceivedForm = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: recordReceived.url(args, options),
        method: 'post',
    })

                    recordReceivedForm.post = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: recordReceived.url(args, options),
            method: 'post',
        })
    
    recordReceived.form = recordReceivedForm
export const history = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(options),
    method: 'get',
})

history.definition = {
    methods: ["get","head"],
    url: '/kasir/goods-in-history',
} satisfies RouteDefinition<["get","head"]>

history.url = (options?: RouteQueryOptions) => {
    return history.definition.url + queryParams(options)
}

history.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(options),
    method: 'get',
})
history.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: history.url(options),
    method: 'head',
})

        const historyForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: history.url(options),
        method: 'get',
    })

                    historyForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: history.url(options),
            method: 'get',
        })
                    historyForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: history.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    history.form = historyForm
export const historyShow = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: historyShow.url(args, options),
    method: 'get',
})

historyShow.definition = {
    methods: ["get","head"],
    url: '/kasir/goods-in-history/{goodsIn}',
} satisfies RouteDefinition<["get","head"]>

historyShow.url = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { goodsIn: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id_pemesanan_barang' in args) {
            args = { goodsIn: args.id_pemesanan_barang }
        }
    
    if (Array.isArray(args)) {
        args = {
                    goodsIn: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        goodsIn: typeof args.goodsIn === 'object'
                ? args.goodsIn.id_pemesanan_barang
                : args.goodsIn,
                }

    return historyShow.definition.url
            .replace('{goodsIn}', parsedArgs.goodsIn.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

historyShow.get = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: historyShow.url(args, options),
    method: 'get',
})
historyShow.head = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: historyShow.url(args, options),
    method: 'head',
})

        const historyShowForm = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: historyShow.url(args, options),
        method: 'get',
    })

                    historyShowForm.get = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: historyShow.url(args, options),
            method: 'get',
        })
                    historyShowForm.head = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: historyShow.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    historyShow.form = historyShowForm
const goodsIn = {
    index: Object.assign(index, index),
create: Object.assign(create, create),
store: Object.assign(store, store),
show: Object.assign(show, show),
items: Object.assign(items, items),
submit: Object.assign(submit, submit),
destroy: Object.assign(destroy, destroy),
receivingShow: Object.assign(receivingShow, receivingShow),
recordReceived: Object.assign(recordReceived, recordReceived),
history: Object.assign(history, history),
historyShow: Object.assign(historyShow, historyShow),
}

export default goodsIn