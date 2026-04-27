import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
export const add = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: add.url(args, options),
    method: 'post',
})

add.definition = {
    methods: ["post"],
    url: '/kasir/goods-in/{goodsIn}/items',
} satisfies RouteDefinition<["post"]>

add.url = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions) => {
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

    return add.definition.url
            .replace('{goodsIn}', parsedArgs.goodsIn.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

add.post = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: add.url(args, options),
    method: 'post',
})

        const addForm = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: add.url(args, options),
        method: 'post',
    })

                    addForm.post = (args: { goodsIn: number | { id_pemesanan_barang: number } } | [goodsIn: number | { id_pemesanan_barang: number } ] | number | { id_pemesanan_barang: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: add.url(args, options),
            method: 'post',
        })
    
    add.form = addForm
export const update = (args: { goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number } | [goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/kasir/goods-in/{goodsIn}/items/{id_detail}',
} satisfies RouteDefinition<["patch"]>

update.url = (args: { goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number } | [goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    goodsIn: args[0],
                    id_detail: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        goodsIn: typeof args.goodsIn === 'object'
                ? args.goodsIn.id_pemesanan_barang
                : args.goodsIn,
                                id_detail: args.id_detail,
                }

    return update.definition.url
            .replace('{goodsIn}', parsedArgs.goodsIn.toString())
            .replace('{id_detail}', parsedArgs.id_detail.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

update.patch = (args: { goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number } | [goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

        const updateForm = (args: { goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number } | [goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

                    updateForm.patch = (args: { goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number } | [goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
export const remove = (args: { goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number } | [goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: remove.url(args, options),
    method: 'delete',
})

remove.definition = {
    methods: ["delete"],
    url: '/kasir/goods-in/{goodsIn}/items/{id_detail}',
} satisfies RouteDefinition<["delete"]>

remove.url = (args: { goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number } | [goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    goodsIn: args[0],
                    id_detail: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        goodsIn: typeof args.goodsIn === 'object'
                ? args.goodsIn.id_pemesanan_barang
                : args.goodsIn,
                                id_detail: args.id_detail,
                }

    return remove.definition.url
            .replace('{goodsIn}', parsedArgs.goodsIn.toString())
            .replace('{id_detail}', parsedArgs.id_detail.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

remove.delete = (args: { goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number } | [goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: remove.url(args, options),
    method: 'delete',
})

        const removeForm = (args: { goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number } | [goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: remove.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

                    removeForm.delete = (args: { goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number } | [goodsIn: number | { id_pemesanan_barang: number }, id_detail: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: remove.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    remove.form = removeForm
const items = {
    add: Object.assign(add, add),
update: Object.assign(update, update),
remove: Object.assign(remove, remove),
}

export default items