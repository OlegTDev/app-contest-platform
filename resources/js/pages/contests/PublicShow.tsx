import { Head, Link, router } from "@inertiajs/react";
import { Button, Card, List, Tag, Typography, Space, Empty, Descriptions, Result } from "antd";
import { ArrowLeftOutlined, QuestionCircleOutlined, ThunderboltOutlined, TrophyOutlined } from "@ant-design/icons";
import dayjs from "dayjs";

const { Title, Text, Paragraph } = Typography;

type QuizActivity = {
    id: number;
    type: "quiz";
    title: string;
    description: string | null;
    is_visible: boolean;
    created_at: string;
};

type VotingActivity = {
    id: number;
    type: "voting";
    title: string;
    description: string | null;
    author_name: string | null;
    author_department: string | null;
    votes_count: number;
    created_at: string;
};

type ActivityItem = QuizActivity | VotingActivity;

type ContestItem = {
    id: number;
    title: string;
    type: string;
    status: string;
    description: string | null;
    start_at: string | null;
    end_at: string | null;
    is_active: boolean;
    is_time_active: boolean;
    activities: ActivityItem[];
    created_at: string;
};

type PublicShowProps = {
    contest: ContestItem;
};

const statusLabels: Record<string, string> = {
    draft: "Черновик",
    published: "Опубликован",
    paused: "На паузе",
    closed: "Закрыт",
};

export default function PublicShow({ contest }: PublicShowProps): React.JSX.Element {
    const handleVoteRedirect = () => {
        router.get(`/contest/${contest.id}/entries/public`, {}, {
            preserveScroll: true,
        });
    };

    const handleQuizRedirect = () => {
        if (contest.activities.length > 0) {
            const firstQuiz = contest.activities.find(
                (a) => a.type === "quiz"
            ) as QuizActivity;
            router.get(`/contest/${contest.id}/quizzes/${firstQuiz.id}/take`, {}, {
                preserveScroll: true,
            });
        }
    };

    // Not time active yet
    if (!contest.is_time_active && contest.is_active === false) {
        return (
            <>
                <Head title={contest.title} />
                <div style={{ maxWidth: 800, margin: "100px auto", textAlign: "center" }}>
                    <Card>
                        <Result
                            status="info"
                            title="Конкурс ещё не начался"
                            subTitle={
                                contest.start_at
                                    ? `Начало: ${dayjs(contest.start_at).format("DD.MM.YYYY HH:mm")}`
                                    : "Следите за обновлениями"
                            }
                            extra={[
                                <Link key="back" href="/contests">
                                    <Button type="primary">К конкурсам</Button>
                                </Link>,
                            ]}
                        />
                    </Card>
                </div>
            </>
        );
    }

    // Contest closed
    if (contest.status === "closed" || contest.status === "paused") {
        return (
            <>
                <Head title={contest.title} />
                <div style={{ maxWidth: 800, margin: "100px auto", textAlign: "center" }}>
                    <Card>
                        <Result
                            status="warning"
                            title="Конкурс завершён"
                            subTitle={`Статус: ${statusLabels[contest.status] || contest.status}`}
                            extra={[
                                <Link key="back" href="/contests">
                                    <Button type="primary">К конкурсам</Button>
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
            <Head title={contest.title} />

            <div style={{ maxWidth: 1000, margin: "0 auto", padding: "24px 0" }}>
                <Space direction="vertical" size="large" style={{ width: "100%" }}>
                    {/* Back button */}
                    <Link href="/contests">
                        <Button type="text" icon={<ArrowLeftOutlined />}>
                            К конкурсам
                        </Button>
                    </Link>

                    {/* Header */}
                    <div>
                        <Space>
                            <Title level={2} style={{ margin: 0 }}>{contest.title}</Title>
                            <Tag color={contest.is_active ? "green" : "default"}>
                                {contest.is_active ? "● Активен" : statusLabels[contest.status] || contest.status}
                            </Tag>
                            <Tag color={contest.type === "quiz" ? "blue" : "green"}>
                                {contest.type === "quiz" ? "Викторина" : "Голосование"}
                            </Tag>
                        </Space>
                        <Paragraph type="secondary" style={{ marginTop: 8 }}>
                            Создан: {dayjs(contest.created_at).format("DD.MM.YYYY HH:mm")}
                        </Paragraph>
                    </div>

                    {/* Description */}
                    {contest.description && (
                        <Card>
                            <Paragraph>{contest.description}</Paragraph>
                        </Card>
                    )}

                    {/* Period */}
                    {(contest.start_at || contest.end_at) && (
                        <Card title="Период проведения">
                            <Descriptions bordered column={1} size="small">
                                <Descriptions.Item label="Начало">
                                    {contest.start_at ? dayjs(contest.start_at).format("DD.MM.YYYY HH:mm") : "Не указано"}
                                </Descriptions.Item>
                                <Descriptions.Item label="Окончание">
                                    {contest.end_at ? dayjs(contest.end_at).format("DD.MM.YYYY HH:mm") : "Не указано"}
                                </Descriptions.Item>
                            </Descriptions>
                        </Card>
                    )}

                    {/* Quiz Activities */}
                    {contest.type === "quiz" && (
                        <Card
                            title={
                                <Space>
                                    <QuestionCircleOutlined style={{ color: "#1677ff" }} />
                                    <span>Вопросы</span>
                                    <Tag>{contest.activities.length}</Tag>
                                </Space>
                            }
                            extra={
                                contest.is_active && contest.activities.length > 0 ? (
                                    <Button
                                        type="primary"
                                        icon={<QuestionCircleOutlined />}
                                        onClick={handleQuizRedirect}
                                    >
                                        Пройти викторину
                                    </Button>
                                ) : null
                            }
                        >
                            {contest.activities.length === 0 ? (
                                <Empty description="Вопросов пока нет" />
                            ) : (
                                <List
                                    dataSource={contest.activities}
                                    renderItem={(activity) => {
                                        if (activity.type !== "quiz") return null;
                                        return (
                                            <List.Item>
                                                <Space
                                                    style={{
                                                        width: "100%",
                                                        justifyContent: "space-between",
                                                    }}
                                                >
                                                    <Space>
                                                        <Text strong>{activity.title}</Text>
                                                        {!activity.is_visible && (
                                                            <Tag color="orange">Скоро</Tag>
                                                        )}
                                                    </Space>
                                                    <Text type="secondary" style={{ fontSize: 12 }}>
                                                        {dayjs(activity.created_at).format("DD.MM.YYYY")}
                                                    </Text>
                                                </Space>
                                            </List.Item>
                                        );
                                    }}
                                />
                            )}
                        </Card>
                    )}

                    {/* Voting Activities */}
                    {contest.type === "voting" && (
                        <Card
                            title={
                                <Space>
                                    <ThunderboltOutlined style={{ color: "#52c41a" }} />
                                    <span>Работы</span>
                                    <Tag>{contest.activities.length}</Tag>
                                </Space>
                            }
                            extra={
                                contest.is_active && contest.activities.length > 0 ? (
                                    <Button
                                        type="primary"
                                        icon={<ThunderboltOutlined />}
                                        onClick={handleVoteRedirect}
                                    >
                                        Голосовать
                                    </Button>
                                ) : null
                            }
                        >
                            {contest.activities.length === 0 ? (
                                <Empty description="Работ пока нет" />
                            ) : (
                                <List
                                    dataSource={contest.activities}
                                    renderItem={(activity) => {
                                        if (activity.type !== "voting") return null;
                                        return (
                                            <List.Item>
                                                <Space
                                                    style={{
                                                        width: "100%",
                                                        justifyContent: "space-between",
                                                    }}
                                                >
                                                    <Space>
                                                        <Text strong>{activity.title}</Text>
                                                        {activity.author_name && (
                                                            <Tag>{activity.author_name}</Tag>
                                                        )}
                                                        <TrophyOutlined style={{ color: "#faad14" }} />
                                                        <Tag color="blue">
                                                            {activity.votes_count}{" "}
                                                            {activity.votes_count === 1
                                                                ? "голос"
                                                                : activity.votes_count < 5
                                                                    ? "голоса"
                                                                    : "голосов"}
                                                        </Tag>
                                                    </Space>
                                                    <Text type="secondary" style={{ fontSize: 12 }}>
                                                        {dayjs(activity.created_at).format("DD.MM.YYYY")}
                                                    </Text>
                                                </Space>
                                            </List.Item>
                                        );
                                    }}
                                />
                            )}
                        </Card>
                    )}
                </Space>
            </div>
        </>
    );
}
