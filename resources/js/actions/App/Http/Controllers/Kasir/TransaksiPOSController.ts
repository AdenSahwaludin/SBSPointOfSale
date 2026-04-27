import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
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
export const getProdukByBarcode = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getProdukByBarcode.url(options),
    method: 'get',
})

getProdukByBarcode.definition = {
    methods: ["get","head"],
    url: '/kasir/pos/produk',
} satisfies RouteDefinition<["get","head"]>

getProdukByBarcode.url = (options?: RouteQueryOptions) => {
    return getProdukByBarcode.definition.url + queryParams(options)
}

getProdukByBarcode.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getProdukByBarcode.url(options),
    method: 'get',
})
getProdukByBarcode.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getProdukByBarcode.url(options),
    method: 'head',
})

        const getProdukByBarcodeForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getProdukByBarcode.url(options),
        method: 'get',
    })

                    getProdukByBarcodeForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getProdukByBarcode.url(options),
            method: 'get',
        })
                    getProdukByBarcodeForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getProdukByBarcode.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    getProdukByBarcode.form = getProdukByBarcodeForm
export const getTransactionReceipt = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getTransactionReceipt.url(args, options),
    method: 'get',
})

getTransactionReceipt.definition = {
    methods: ["get","head"],
    url: '/kasir/pos/receipt/{nomorTransaksi}',
} satisfies RouteDefinition<["get","head"]>

getTransactionReceipt.url = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return getTransactionReceipt.definition.url
            .replace('{nomorTransaksi}', parsedArgs.nomorTransaksi.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

getTransactionReceipt.get = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getTransactionReceipt.url(args, options),
    method: 'get',
})
getTransactionReceipt.head = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getTransactionReceipt.url(args, options),
    method: 'head',
})

        const getTransactionReceiptForm = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getTransactionReceipt.url(args, options),
        method: 'get',
    })

                    getTransactionReceiptForm.get = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getTransactionReceipt.url(args, options),
            method: 'get',
        })
                    getTransactionReceiptForm.head = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getTransactionReceipt.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    getTransactionReceipt.form = getTransactionReceiptForm
export const getTodayTransactions = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getTodayTransactions.url(options),
    method: 'get',
})

getTodayTransactions.definition = {
    methods: ["get","head"],
    url: '/kasir/pos/today-transactions',
} satisfies RouteDefinition<["get","head"]>

getTodayTransactions.url = (options?: RouteQueryOptions) => {
    return getTodayTransactions.definition.url + queryParams(options)
}

getTodayTransactions.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getTodayTransactions.url(options),
    method: 'get',
})
getTodayTransactions.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getTodayTransactions.url(options),
    method: 'head',
})

        const getTodayTransactionsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getTodayTransactions.url(options),
        method: 'get',
    })

                    getTodayTransactionsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getTodayTransactions.url(options),
            method: 'get',
        })
                    getTodayTransactionsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getTodayTransactions.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    getTodayTransactions.form = getTodayTransactionsForm
export const cancelTransaction = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancelTransaction.url(args, options),
    method: 'post',
})

cancelTransaction.definition = {
    methods: ["post"],
    url: '/kasir/pos/cancel/{nomorTransaksi}',
} satisfies RouteDefinition<["post"]>

cancelTransaction.url = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return cancelTransaction.definition.url
            .replace('{nomorTransaksi}', parsedArgs.nomorTransaksi.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

cancelTransaction.post = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancelTransaction.url(args, options),
    method: 'post',
})

        const cancelTransactionForm = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: cancelTransaction.url(args, options),
        method: 'post',
    })

                    cancelTransactionForm.post = (args: { nomorTransaksi: string | number } | [nomorTransaksi: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: cancelTransaction.url(args, options),
            method: 'post',
        })
    
    cancelTransaction.form = cancelTransactionForm
const TransaksiPOSController = { index, store, searchProduk, getProdukByBarcode, getTransactionReceipt, getTodayTransactions, cancelTransaction }

export default TransaksiPOSController