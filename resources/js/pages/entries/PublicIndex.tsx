import { Head, Link, usePage } from "@inertiajs/react";
import { Button, Card, List, Result, Space, Typography, Tag, message } from "antd";
import { HeartOutlined, HeartFilled, TrophyOutlined, ArrowLeftOutlined } from "@ant-design/icons";
import dayjs from "dayjs";

const { Title, Text, Paragraph } = Typography;

// Show flash messages
const { flash } = usePage<{ flash?: { success?: string; error?: string } }>().props;
if (flash?.success) message.success(flash.success);
if (flash?.error) message.error(flash.error);

type EntryItem = {
    id: number;
    title: string;
    description: string | null;
    fields_data: Record<string, unknown> | null;
    votes_count: number;
    author: {
        id: number;
        name: string;
    } | null;
    is_voted: boolean;
};

type ContestItem = {
    id: number;
    title: string;
    type: string;
};

type PublicIndexProps = {
    contest: ContestItem;
    entries: EntryItem[];
};

export default function PublicIndex({ contest, entries }: PublicIndexProps): React.JSX.Element {
    const { props } = usePage();
    const csrfToken = props?.csrf_token ?? '';
    const sortedEntries = [...entries].sort((a, b) => b.votes_count - a.votes_count);

    if (entries.length === 0) {
        return (
            <>
                <Head title="Голосование" />
                <div style={{ maxWidth: 800, margin: "100px auto", textAlign: "center" }}>
                    <Card>
                        <Result
                            status="info"
                            title="Пока нет работ для голосования"
                            subTitle="Карточки будут добавлены организатором"
                            extra={[
                                <Link key="back" href={`/contest/${contest.id}`}>
                                    <Button type="primary">К конкурсу</Button>
                                </Link>,
                            ]}
                        />
                    </Card>
                </div>
            </>
        );
    }

    return (
        <>
            <Head title={`Голосование: ${contest.title}`} />

            <div style={{ maxWidth: 1000, margin: "0 auto", padding: "24px 0" }}>
                <Space direction="vertical" size="large" style={{ width: "100%" }}>
                    <div>
                        <Link href={`/contest/${contest.id}`}>
                            <Button type="text" icon={<ArrowLeftOutlined />} style={{ float: "left", marginRight: 12 }}>
                                Назад
                            </Button>
                        </Link>
                        <Title level={2}>{contest.title}</Title>
                        <Paragraph type="secondary">
                            Проголосуйте за лучшую работу!
                        </Paragraph>
                    </div>

                    {/* Leaderboard */}
                    <Card title={<Space><TrophyOutlined style={{ color: "#faad14" }} /> Лидеры</Space>}>
                        <List
                            dataSource={sortedEntries.slice(0, 3)}
                            renderItem={(entry, index) => (
                                <List.Item>
                                    <Space style={{ width: "100%", justifyContent: "space-between" }}>
                                        <Space>
                                            {index === 0 && <span style={{ fontSize: 24 }}>🥇</span>}
                                            {index === 1 && <span style={{ fontSize: 24 }}>🥈</span>}
                                            {index === 2 && <span style={{ fontSize: 24 }}>🥉</span>}
                                            <div>
                                                <Text strong>{entry.title}</Text>
                                                {entry.author && (
                                                    <br />
                                                )}
                                                {entry.author && (
                                                    <Text type="secondary" style={{ fontSize: 12 }}>
                                                        {entry.author.name}
                                                    </Text>
                                                )}
                                            </div>
                                        </Space>
                                        <Tag color="blue">{entry.votes_count} голосов</Tag>
                                    </Space>
                                </List.Item>
                            )}
                        />
                    </Card>

                    {/* All entries */}
                    <Card title={`Все работы (${entries.length})`}>
                        <List
                            grid={{ gutter: 16, xs: 1, sm: 2, md: 2, lg: 3 }}
                            dataSource={entries}
                            renderItem={(entry) => (
                                <List.Item>
                                    <Card
                                        hoverable
                                        style={{ width: "100%", height: "100%", display: "flex", flexDirection: "column" }}
                                        cover={
                                            <div style={{
                                                height: 160,
                                                background: "#f5f5f5",
                                                display: "flex",
                                                alignItems: "center",
                                                justifyContent: "center",
                                                color: "#999",
                                                fontSize: 14,
                                            }}>
                                                {entry.fields_data?.main_image ? (
                                                    <img
                                                        src={entry.fields_data.main_image as string}
                                                        alt={entry.title}
                                                        style={{ width: "100%", height: "100%", objectFit: "cover" }}
                                                    />
                                                ) : (
                                                    "Изображение работы"
                                                )}
                                            </div>
                                        }
                                    >
                                        <Space direction="vertical" style={{ width: "100%", flex: 1 }}>
                                            <Title level={5} style={{ marginBottom: 4 }}>
                                                {entry.title}
                                            </Title>

                                            {entry.description && (
                                                <Paragraph
                                                    type="secondary"
                                                    ellipsis={{ rows: 2 }}
                                                    style={{ marginBottom: 8 }}
                                                >
                                                    {entry.description}
                                                </Paragraph>
                                            )}

                                            {entry.author && (
                                                <Space size="small" style={{ marginBottom: 8 }}>
                                                    <Text type="secondary" style={{ fontSize: 12 }}>
                                                        Автор: {entry.author.name}
                                                    </Text>
                                                </Space>
                                            )}

                                            <Space style={{ marginTop: "auto" }}>
                                                {entry.is_voted ? (
                                                    <form action={`/contest/${contest.id}/entries/${entry.id}/vote`} method="post">
                                                        <input type="hidden" name="_method" value="DELETE" />
                                                        <input type="hidden" name="_token" defaultValue={csrfToken} />
                                                        <Button
                                                            type="primary"
                                                            icon={<HeartFilled />}
                                                            htmlType="submit"
                                                            style={{ flex: 1 }}
                                                        >
                                                            {entry.votes_count}
                                                        </Button>
                                                    </form>
                                                ) : (
                                                    <form action={`/contest/${contest.id}/entries/${entry.id}/vote`} method="post">
                                                        <input type="hidden" name="_token" defaultValue={csrfToken} />
                                                        <Button
                                                            type="default"
                                                            icon={<HeartOutlined />}
                                                            htmlType="submit"
                                                            style={{ flex: 1 }}
                                                        >
                                                            {entry.votes_count}
                                                        </Button>
                                                    </form>
                                                )}
                                            </Space>
                                        </Space>
                                    </Card>
                                </List.Item>
                            )}
                        />
                    </Card>
                </Space>
            </div>
        </>
    );
}
