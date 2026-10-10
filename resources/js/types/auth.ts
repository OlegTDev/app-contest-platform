export type User = {
    id: number;
    name: string;
    email: string;
    guid: string | null;
    login: string;
    domain: string | null;
    department: string | null;
    position: string | null;
    city_code: string | null;
    phone: string | null;
    role: string;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

