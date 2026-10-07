import { Head, Link } from "@inertiajs/react";
import { Card, Row, Col, Typography, Tag, Empty, Space, Button } from "antd";
import { TrophyOutlined, QuestionCircleOutlined, ThunderboltOutlined } from "@ant-design/icons";
import dayjs from "dayjs";

const { Title, Text, Paragraph } = Typography;

type ContestItem = {
    id: number;
    title: string;
    type: string;
    description: string | null;
    start_at: string | null;
    end_at: string | null;
    quiz_entry_count: number;
    entry_count: number;
    created_at: string;
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
                        <Row gutter={[24, 24]}>
                            {contests.map((contest) => (
                                <Col xs={24} sm={12} lg={8} key={contest.id}>
                                    <Link href={`/contests/${contest.id}`}>
                                        <Card
                                            hoverable
                                            style={{
                                                height: "100%",
                                                display: "flex",
                                                flexDirection: "column",
                                                border: "1px solid #f0f0f0",
                                                transition: "all 0.3s",
                                            }}
                                            styles={{
                                                body: {
                                                    flex: 1,
                                                    display: "flex",
                                                    flexDirection: "column",
                                                },
                                            }}
                                        >
                                            {/* Icon and Type */}
                                            <div
                                                style={{
                                                    display: "flex",
                                                    justifyContent: "space-between",
                                                    alignItems: "flex-start",
                                                    marginBottom: 16,
                                                }}
                                            >
                                                <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
                                                    {getTypeIcon(contest.type)}
                                                    <Tag color={getTypeColor(contest.type)}>
                                                        {getTypeLabel(contest.type)}
                                                    </Tag>
                                                </div>
                                                <Text type="secondary" style={{ fontSize: 12 }}>
                                                    {dayjs(contest.created_at).format("DD.MM.YYYY")}
                                                </Text>
                                            </div>

                                            {/* Title */}
                                            <Title level={4} style={{ margin: "0 0 12px 0" }}>
                                                {contest.title}
                                            </Title>

                                            {/* Description */}
                                            {contest.description && (
                                                <Paragraph
                                                    type="secondary"
                                                    ellipsis={{ rows: 3 }}
                                                    style={{
                                                        flex: 1,
                                                        marginBottom: 16,
                                                    }}
                                                >
                                                    {contest.description}
                                                </Paragraph>
                                            )}

                                            {/* Stats */}
                                            <div
                                                style={{
                                                    display: "flex",
                                                    justifyContent: "space-between",
                                                    padding: "12px 0",
                                                    borderTop: "1px solid #f0f0f0",
                                                    marginTop: "auto",
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
                                            </div>

                                            {/* Period */}
                                            {(contest.start_at || contest.end_at) && (
                                                <div
                                                    style={{
                                                        marginTop: 8,
                                                        padding: "8px 12px",
                                                        background: "#f6ffed",
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
                                        </Card>
                                    </Link>
                                </Col>
                            ))}
                        </Row>
                    )}
                </Space>
            </div>
        </>
    );
}
