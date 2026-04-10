export type UserRole = 'employee' | 'superuser' | 'admin';

export type User = {
    id: number;
    name: string;
    email: string;
    npk: string | null;
    divisi: string | null;
    departement: string | null;
    section: string | null;
    role: UserRole;
    is_active: boolean;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export type UserFormData = {
    name: string;
    email: string;
    npk?: string;
    divisi?: string;
    departement?: string;
    section?: string;
    password?: string;
    role: UserRole;
    is_active: boolean;
};

export type UserListFilters = {
    search: string;
    role: UserRole | '';
    status: 'active' | 'inactive' | '';
};
