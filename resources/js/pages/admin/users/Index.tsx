import { Head, router } from "@inertiajs/react";
import { Card, Select, Table, Tag, Typography, Space, message } from "antd";
import type { ColumnsType } from "antd/es/table";
import dayjs from "dayjs";

const { Title, Text } = Typography;

type UserRole = "admin" | "moderator" | "user";

type UserItem = {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    created_at: string;
};

type PaginationInfo = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    next_page_url: string | null;
    prev_page_url: string | null;
};

type RoleOption = {
    value: string;
    label: string;
};

type IndexProps = {
    users: UserItem[];
    pagination: PaginationInfo;
    available_roles: RoleOption[];
};

const roleTagColor: Record<UserRole, string> = {
    admin: "red",
    moderator: "orange",
    user: "default",
};

export default function UsersIndex({
    users,
    pagination,
    available_roles,
}: IndexProps): React.JSX.Element {
    const handleRoleChange = (userId: number, newRole: UserRole): void => {
        router.patch(
            `/admin/users/${userId}/role`,
            { role: newRole },
            {
                preserveScroll: true,
                onSuccess: () => {
                    message.success("Роль успешно обновлена.");
                },
                onError: () => {
                    message.error("Ошибка при обновлении роли.");
                },
            }
        );
    };

    const columns: ColumnsType<UserItem> = [
        {
            title: "Имя",
            dataIndex: "name",
            key: "name",
            width: 200,
            render: (name: string) => <Text strong>{name}</Text>,
        },
        {
            title: "Email",
            dataIndex: "email",
            key: "email",
            ellipsis: true,
        },
        {
            title: "Роль",
            dataIndex: "role",
            key: "role",
            width: 160,
            render: (role: UserRole) => (
                <Tag color={roleTagColor[role]} variant="outlined" size="small">
                    {role}
                </Tag>
            ),
        },
        {
            title: "Дата регистрации",
            dataIndex: "created_at",
            key: "created_at",
            width: 180,
            render: (date: string) =>
                dayjs(date).format("DD.MM.YYYY HH:mm"),
        },
        {
            title: "Действия",
            key: "actions",
            width: 220,
            render: (_: unknown, record: UserItem) => (
                <Select<UserRole>
                    value={record.role}
                    onChange={(value: UserRole) =>
                        handleRoleChange(record.id, value)
                    }
                    options={available_roles}
                    size="medium"
                    style={{ width: 160 }}
                    variant="outlined"
                />
            ),
        },
    ];

    return (
        <>
            <Head title="Управление пользователями" />

            <div style={{ maxWidth: "1200px", margin: "0 auto", padding: "24px 0" }}>
                <Space direction="vertical" size="large" style={{ width: "100%" }}>
                    <Title level={2}>Управление пользователями</Title>

                    <Card>
                        <Table<UserItem>
                            columns={columns}
                            dataSource={users}
                            rowKey="id"
                            pagination={{
                                current: pagination.current_page,
                                pageSize: pagination.per_page,
                                total: pagination.total,
                                pageSizeOptions: ["10", "15", "25", "50"],
                                showSizeChanger: true,
                                showTotal: (total) => `Всего: ${total}`,
                                onChange: (page, pageSize) => {
                                    const params = new URLSearchParams(
                                        window.location.search
                                    );
                                    params.set("page", String(page));
                                    if (pageSize) {
                                        params.set("per_page", String(pageSize));
                                    }
                                    router.get(
                                        `/admin/users?${params.toString()}`,
                                        {},
                                        { preserveScroll: true, preserveState: true }
                                    );
                                },
                            }}
                            locale={{ emptyText: "Пользователи не найдены" }}
                        />
                    </Card>
                </Space>
            </div>
        </>
    );
}
