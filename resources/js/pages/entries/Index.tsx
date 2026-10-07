import { Head, Link } from "@inertiajs/react";
import { Button, Card, List, Table, Tag, Typography, Space, Divider } from "antd";
import { PlusOutlined, EditOutlined, DeleteOutlined, EyeOutlined } from "@ant-design/icons";
import dayjs from "dayjs";

const { Title, Text, Paragraph } = Typography;

type EntryItem = {
    id: number;
    title: string;
    description: string | null;
    votes_count: number;
    is_scheduled: boolean;
    created_at: string;
};

type ContestItem = {
    id: number;
    title: string;
    type: string;
};

type EntriesIndexProps = {
    contest: ContestItem;
    entries: EntryItem[];
};

export default function EntriesIndex({ contest, entries }: EntriesIndexProps): React.JSX.Element {
    const columns = [
        {
            title: "Название",
            dataIndex: "title",
            key: "title",
            render: (title: string, record: EntryItem) => (
                <Space>
                    <Text strong>{title}</Text>
                    {record.is_scheduled && (
                        <Tag color="blue">Таймер</Tag>
                    )}
                </Space>
            ),
        },
        {
            title: "Описание",
            dataIndex: "description",
            key: "description",
            ellipsis: true,
            render: (desc: string | null) => desc || "—",
        },
        {
            title: "Голосов",
            dataIndex: "votes_count",
            key: "votes_count",
            width: 100,
            render: (count: number) => count,
        },
        {
            title: "Создана",
            dataIndex: "created_at",
            key: "created_at",
            width: 160,
            render: (date: string) => dayjs(date).format("DD.MM.YYYY HH:mm"),
        },
        {
            title: "Действия",
            key: "actions",
            width: 200,
            render: (_: unknown, record: EntryItem) => (
                <Space size="small">
                    <Link href={`/contest/${contest.id}/entries/${record.id}/edit`}>
                        <Button size="small" icon={<EditOutlined />}>
                            Изменить
                        </Button>
                    </Link>
                    <Button size="small" danger icon={<DeleteOutlined />} />
                </Space>
            ),
        },
    ];

    return (
        <>
            <Head title={`Карточки: ${contest.title}`} />

            <div style={{ maxWidth: 1200, margin: "0 auto", padding: "24px 0" }}>
                <Space direction="vertical" size="large" style={{ width: "100%" }}>
                    <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
                        <div>
                            <Link href={`/contest/${contest.id}`}>
                                <Button type="text" style={{ float: "left", marginRight: 12 }}>
                                    ← Назад
                                </Button>
                            </Link>
                            <Title level={2} style={{ margin: 0 }}>
                                Карточки
                            </Title>
                            <Paragraph type="secondary">
                                {contest.title} | Всего: {entries.length}
                            </Paragraph>
                        </div>
                        <Link href={`/contest/${contest.id}/entries/create`}>
                            <Button type="primary" icon={<PlusOutlined />}>
                                Добавить карточку
                            </Button>
                        </Link>
                    </div>

                    <Card>
                        <Table
                            columns={columns}
                            dataSource={entries}
                            rowKey="id"
                            pagination={false}
                            locale={{ emptyText: "Карточек пока нет" }}
                        />
                    </Card>
                </Space>
            </div>
        </>
    );
}
