import { Head, Link, router } from "@inertiajs/react";
import { Button, Card, List, Tag, Typography, Space, Empty, Descriptions, Result, Modal, Image, Row, Col } from "antd";
import { ArrowLeftOutlined, QuestionCircleOutlined, ThunderboltOutlined, TrophyOutlined, EyeOutlined } from "@ant-design/icons";
import dayjs from "dayjs";
import { useState } from 'react';

const { Title, Text, Paragraph } = Typography;

type MediaItem = {
    id: number;
    file_name: string;
    file_url: string;
    file_type: string;
    file_extension: string;
    file_size: number | null;
    created_at: string;
};

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
    media: MediaItem[];
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

const getFileIcon = (type: string) => {
    switch (type) {
        case 'image':
            return '🖼️';
        case 'video':
            return '🎥';
        case 'pdf':
            return '📄';
        case 'document':
            return '📎';
        default:
            return '📁';
    }
};

export default function PublicShow({ contest }: PublicShowProps): React.JSX.Element {
    const [previewOpen, setPreviewOpen] = useState(false);
    const [previewFile, setPreviewFile] = useState<MediaItem | null>(null);

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

    const handlePreview = (file: MediaItem) => {
        setPreviewFile(file);
        setPreviewOpen(true);
    };

    const renderPreview = () => {
        if (!previewFile) return null;

        switch (previewFile.file_type) {
            case 'image':
                return (
                    <Image
                        src={previewFile.file_url}
                        style={{ maxWidth: '100%', maxHeight: '70vh' }}
                    />
                );
            case 'video':
                return (
                    <video
                        controls
                        style={{ maxWidth: '100%', maxHeight: '70vh' }}
                        src={previewFile.file_url}
                    >
                        Ваш браузер не поддерживает видео.
                    </video>
                );
            case 'pdf':
                return (
                    <iframe
                        src={previewFile.file_url}
                        style={{ width: '100%', height: '70vh', border: 'none' }}
                        title="PDF Preview"
                    />
                );
            default:
                return (
                    <div style={{ textAlign: 'center', padding: '40px 0' }}>
                        <p>Предпросмотр недоступен для этого типа файла</p>
                        <Button type="primary" href={previewFile.file_url} download>
                            Скачать файл
                        </Button>
                    </div>
                );
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

            <div style={{ maxWidth: 1200, margin: "0 auto", padding: "24px 0" }}>
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
                                <Row gutter={[16, 16]}>
                                    {contest.activities.map((activity) => {
                                        if (activity.type !== "voting") return null;
                                        const hasMedia = activity.media && activity.media.length > 0;

                                        return (
                                            <Col xs={24} sm={12} lg={8} key={activity.id}>
                                                <Card
                                                    hoverable
                                                    style={{ height: '100%' }}
                                                    cover={
                                                        hasMedia ? (
                                                            <div style={{ height: 200, background: '#f5f5f5', display: 'flex', alignItems: 'center', justifyContent: 'center', position: 'relative' }}>
                                                                {activity.media[0].file_type === 'image' ? (
                                                                    <img
                                                                        src={activity.media[0].file_url}
                                                                        alt={activity.title}
                                                                        style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                                                                    />
                                                                ) : (
                                                                    <div style={{ textAlign: 'center' }}>
                                                                        <span style={{ fontSize: 48 }}>{getFileIcon(activity.media[0].file_type)}</span>
                                                                        <p style={{ margin: '8px 0 0', fontSize: 12, color: '#999' }}>
                                                                            {activity.media[0].file_name}
                                                                        </p>
                                                                    </div>
                                                                )}
                                                                <Button
                                                                    type="primary"
                                                                    size="small"
                                                                    icon={<EyeOutlined />}
                                                                    style={{ position: 'absolute', bottom: 8, right: 8 }}
                                                                    onClick={(e) => {
                                                                        e.preventDefault();
                                                                        e.stopPropagation();
                                                                        handlePreview(activity.media[0]);
                                                                    }}
                                                                >
                                                                    Просмотр
                                                                </Button>
                                                            </div>
                                                        ) : (
                                                            <div style={{ height: 200, background: '#f5f5f5', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#999' }}>
                                                                Нет медиа
                                                            </div>
                                                        )
                                                    }
                                                >
                                                    <Space direction="vertical" style={{ width: '100%' }}>
                                                        <Title level={5} style={{ marginBottom: 4 }}>
                                                            {activity.title}
                                                        </Title>
                                                        {activity.description && (
                                                            <Paragraph
                                                                type="secondary"
                                                                ellipsis={{ rows: 3 }}
                                                                style={{ marginBottom: 8 }}
                                                            >
                                                                {activity.description}
                                                            </Paragraph>
                                                        )}
                                                        {activity.author_name && (
                                                            <Tag style={{ marginBottom: 8 }}>{activity.author_name}</Tag>
                                                        )}
                                                        {hasMedia && (
                                                            <Tag color="blue" style={{ marginBottom: 8 }}>
                                                                📎 {activity.media.length} файл(ов)
                                                            </Tag>
                                                        )}
                                                        <Space>
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
                                                    </Space>
                                                </Card>
                                            </Col>
                                        );
                                    })}
                                </Row>
                            )}
                        </Card>
                    )}
                </Space>
            </div>

            {/* Preview Modal */}
            <Modal
                title={previewFile?.file_name}
                open={previewOpen}
                footer={null}
                onCancel={() => setPreviewOpen(false)}
                width="80%"
                style={{ maxWidth: 1200 }}
            >
                {renderPreview()}
            </Modal>
        </>
    );
}
