import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/kasir/pos',
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
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/kasir/pos',
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
export const searchProduk = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: searchProduk.url(options),
    method: 'get',
})

searchProduk.definition = {
    methods: ["get","head"],
    url: '/kasir/pos/search-produk',
} satisfies RouteDefinition<["get","head"]>

searchProduk.url = (options?: RouteQueryOptions) => {
    return searchProduk.definition.url + queryParams(options)
}

searchProduk.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: searchProduk.url(options),
    method: 'get',
})
searchProduk.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: searchProduk.url(options),
    method: 'head',
})

        const searchProdukForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: searchProduk.url(options),
        method: 'get',
    })

                    searchProdukForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: searchProduk.url(options),
            method: 'get',
        })
                    searchProdukForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: searchProduk.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    searchProduk.form = searchProdukForm
export const produk = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: produk.url(options),
    method: 'get',
})

produk.definition = {
    methods: ["get","head"],
    url: '/kasir/pos/produk',
} satisfies RouteDefinition<["get","head"]>

produk.url = (options?: RouteQueryOptions) => {
    return produk.definition.url + queryParams(options)
}

produk.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: produk.url(options),
    method: 'get',
})
produk.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: produk.url(options),
    method: 'head',
})

        const produkForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: produk.url(options),
        method: 'get',
    })

                    produkForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: produk.url(options),
            method: 'get',
        })
                    produkForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: produk.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    produk.form = produkForm
export const receipt = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: receipt.url(args, options),
    method: 'get',
})

receipt.definition = {
    methods: ["get","head"],
    url: '/kasir/pos/receipt/{nomorTransaksi}',
} satisfies RouteDefinition<["get","head"]>

receipt.url = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { nomorTransaksi: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    nomorTransaksi: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        nomorTransaksi: args.nomorTransaksi,
                }

    return receipt.definition.url
            .replace('{nomorTransaksi}', parsedArgs.nomorTransaksi.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

receipt.get = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: receipt.url(args, options),
    method: 'get',
})
receipt.head = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: receipt.url(args, options),
    method: 'head',
})

        const receiptForm = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: receipt.url(args, options),
        method: 'get',
    })

                    receiptForm.get = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: receipt.url(args, options),
            method: 'get',
        })
                    receiptForm.head = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: receipt.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    receipt.form = receiptForm
export const today = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: today.url(options),
    method: 'get',
})

today.definition = {
    methods: ["get","head"],
    url: '/kasir/pos/today-transactions',
} satisfies RouteDefinition<["get","head"]>

today.url = (options?: RouteQueryOptions) => {
    return today.definition.url + queryParams(options)
}

today.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: today.url(options),
    method: 'get',
})
today.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: today.url(options),
    method: 'head',
})

        const todayForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: today.url(options),
        method: 'get',
    })

                    todayForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: today.url(options),
            method: 'get',
        })
                    todayForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: today.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    today.form = todayForm
export const cancel = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancel.url(args, options),
    method: 'post',
})

cancel.definition = {
    methods: ["post"],
    url: '/kasir/pos/cancel/{nomorTransaksi}',
} satisfies RouteDefinition<["post"]>

cancel.url = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { nomorTransaksi: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    nomorTransaksi: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        nomorTransaksi: args.nomorTransaksi,
                }

    return cancel.definition.url
            .replace('{nomorTransaksi}', parsedArgs.nomorTransaksi.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

cancel.post = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancel.url(args, options),
    method: 'post',
})

        const cancelForm = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: cancel.url(args, options),
        method: 'post',
    })

                    cancelForm.post = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: cancel.url(args, options),
            method: 'post',
        })
    
    cancel.form = cancelForm
const pos = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
searchProduk: Object.assign(searchProduk, searchProduk),
produk: Object.assign(produk, produk),
receipt: Object.assign(receipt, receipt),
today: Object.assign(today, today),
cancel: Object.assign(cancel, cancel),
}

export default pos