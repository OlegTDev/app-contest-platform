import { Head, Link } from "@inertiajs/react";
import { Card, Typography, Tag, Empty, Space, Button } from "antd";
import { TrophyOutlined, QuestionCircleOutlined, ThunderboltOutlined, FolderOutlined } from "@ant-design/icons";
import dayjs from "dayjs";

const { Title, Text, Paragraph } = Typography;

type ContestItem = {
    id: number;
    title: string;
    type: string;
    slug: string | null;
    description: string | null;
    start_at: string | null;
    end_at: string | null;
    quiz_entry_count: number;
    entry_count: number;
    created_at: string;
    is_archived: boolean;
};

type PublicIndexProps = {
    contests: ContestItem[];
};

const getTypeIcon = (type: string) => {
    return type === "quiz" ? (
        <QuestionCircleOutlined style={{ fontSize: 24, color: "#1677ff" }} />
    ) : (
        <ThunderboltOutlined style={{ fontSize: 24, color: "#52c41a" }} />
    );
};

const getTypeLabel = (type: string) => {
    return type === "quiz" ? "Викторина" : "Голосование";
};

const getTypeColor = (type: string) => {
    return type === "quiz" ? "blue" : "green";
};

export default function PublicIndex({ contests }: PublicIndexProps): React.JSX.Element {
    return (
        <>
            <Head title="Активные конкурсы" />

            <div style={{ maxWidth: 1200, margin: "0 auto", padding: "24px 0" }}>
                <Space direction="vertical" size="large" style={{ width: "100%" }}>
                    {/* Header */}
                    <div style={{ textAlign: "center", padding: "32px 0" }}>
                        <TrophyOutlined style={{ fontSize: 48, color: "#faad14", marginBottom: 16 }} />
                        <Title level={1} style={{ margin: 0 }}>
                            Активные конкурсы
                        </Title>
                        <Paragraph type="secondary" style={{ fontSize: 16, marginTop: 8 }}>
                            Выберите конкурс и примите участие
                        </Paragraph>
                    </div>

                    {/* Contest Cards */}
                    {contests.length === 0 ? (
                        <Card>
                            <Empty
                                description={
                                    <Space direction="vertical">
                                        <Text>Активных конкурсов пока нет</Text>
                                        <Text type="secondary">
                                            Новые конкурсы будут добавлены в ближайшее время
                                        </Text>
                                    </Space>
                                }
                            />
                        </Card>
                    ) : (
                        <Space direction="vertical" size="large" style={{ width: "100%" }}>
                            {contests.map((contest) => (
                                <Link href={contest.slug ? `/contests/${contest.slug}` : `/contests/${contest.id}`}>
                                    <Card
                                        hoverable
                                        style={{
                                            width: "100%",
                                            border: "1px solid #f0f0f0",
                                            transition: "all 0.3s",
                                        }}
                                        styles={{
                                            body: {
                                                display: "flex",
                                                alignItems: "center",
                                                gap: 24,
                                            },
                                        }}
                                    >
                                        {/* Icon and Type */}
                                        <div
                                            style={{
                                                display: "flex",
                                                alignItems: "center",
                                                gap: 12,
                                                minWidth: 200,
                                            }}
                                        >
                                            {getTypeIcon(contest.type)}
                                            <Tag color={getTypeColor(contest.type)}>
                                                {getTypeLabel(contest.type)}
                                            </Tag>
                                            {contest.is_archived && (
                                                <Tag color="default" icon={<FolderOutlined />}>
                                                    Архив
                                                </Tag>
                                            )}
                                        </div>

                                        {/* Title and Description */}
                                        <div style={{ flex: 1 }}>
                                            <Title level={4} style={{ margin: "0 0 8px 0" }}>
                                                {contest.title}
                                            </Title>
                                            {contest.description && (
                                                <Paragraph
                                                    type="secondary"
                                                    ellipsis={{ rows: 2 }}
                                                    style={{ margin: 0 }}
                                                >
                                                    {contest.description}
                                                </Paragraph>
                                            )}
                                        </div>

                                        {/* Stats and Period */}
                                        <div
                                            style={{
                                                display: "flex",
                                                flexDirection: "column",
                                                alignItems: "flex-end",
                                                gap: 8,
                                                minWidth: 180,
                                            }}
                                        >
                                            <Space>
                                                {contest.type === "quiz" ? (
                                                    <>
                                                        <QuestionCircleOutlined style={{ color: "#999" }} />
                                                        <Text type="secondary">
                                                            {contest.quiz_entry_count}{" "}
                                                            {contest.quiz_entry_count === 1
                                                                ? "вопрос"
                                                                : contest.quiz_entry_count < 5
                                                                    ? "вопроса"
                                                                    : "вопросов"}
                                                        </Text>
                                                    </>
                                                ) : (
                                                    <>
                                                        <ThunderboltOutlined style={{ color: "#999" }} />
                                                        <Text type="secondary">
                                                            {contest.entry_count}{" "}
                                                            {contest.entry_count === 1
                                                                ? "работа"
                                                                : "работ"}
                                                        </Text>
                                                    </>
                                                )}
                                            </Space>
                                            {(contest.start_at || contest.end_at) && (
                                                <div
                                                    style={{
                                                        padding: "4px 12px",
                                                        background: contest.is_archived ? "#f5f5f5" : "#f6ffed",
                                                        borderRadius: 6,
                                                        fontSize: 12,
                                                    }}
                                                >
                                                    <Text type="secondary">
                                                        {contest.start_at
                                                            ? dayjs(contest.start_at).format("DD.MM.YYYY HH:mm")
                                                            : "—"}{" "}
                                                        —{" "}
                                                        {contest.end_at
                                                            ? dayjs(contest.end_at).format("DD.MM.YYYY HH:mm")
                                                            : "—"}
                                                    </Text>
                                                </div>
                                            )}
                                        </div>

                                        {/* Created Date */}
                                        <Text type="secondary" style={{ fontSize: 12, minWidth: 100, textAlign: "right" }}>
                                            {dayjs(contest.created_at).format("DD.MM.YYYY")}
                                        </Text>
                                    </Card>
                                </Link>
                            ))}
                        </Space>
                    )}
                </Space>
            </div>
        </>
    );
}
