export interface Auth {
    user: User;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
};

export interface User {
    id_pengguna: string;
    nama: string;
    email: string;
    telepon?: string;
    role: 'kasir' | 'admin';
    terakhir_login: string | null;
    created_at: string;
    updated_at: string;
}
