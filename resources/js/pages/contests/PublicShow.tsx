import { Head, Link, router } from "@inertiajs/react";
import { Button, Card, List, Tag, Typography, Space, Empty, Descriptions, Result, Modal, Image, Row, Col, Drawer, message } from "antd";
import { ArrowLeftOutlined, QuestionCircleOutlined, ThunderboltOutlined, TrophyOutlined, EyeOutlined, PlusOutlined, PaperClipOutlined, CloseOutlined, DownloadOutlined, FileImageOutlined, FilePdfOutlined, FileOutlined, VideoCameraOutlined, HeartOutlined, HeartFilled } from "@ant-design/icons";
import dayjs from "dayjs";
import { useState, useRef } from 'react';
import { vote } from "@/routes/entries";
import { destroy as voteDestroy } from "@/routes/entries/vote";

const { Title, Text, Paragraph } = Typography;

type MediaItem = {
    id: number;
    file_name: string;
    file_url: string;
    file_type: string;
    file_extension: string;
    file_size: number | null;
    is_main: boolean;
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
    is_voted: boolean;
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

export default function PublicShow({ contest }: PublicShowProps): React.JSX.Element {
    const [previewOpen, setPreviewOpen] = useState(false);
    const [previewFile, setPreviewFile] = useState<MediaItem | null>(null);
    const [mediaDrawerOpen, setMediaDrawerOpen] = useState(false);
    const [selectedEntry, setSelectedEntry] = useState<VotingActivity | null>(null);
    const [uploading, setUploading] = useState(false);
    const [voting, setVoting] = useState(false);
    const [selectedFiles, setSelectedFiles] = useState<File[]>([]);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const maxUploadSizeMB = 100;

    const activeEntry =
        contest.activities.find(
            (a): a is VotingActivity => a.type === "voting" && a.id === selectedEntry?.id
        ) ?? null;

    const handleVote = (entry: VotingActivity) => {
        if (!contest.is_active) {
            message.warning("Голосование сейчас недоступно");
            return;
        }

        setVoting(true);

        const options = {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                setVoting(false);
                message.success(entry.is_voted ? "Голос удалён" : "Голос засчитан!");
            },
            onError: () => {
                setVoting(false);
                message.error("Не удалось проголосовать");
            },
            onFinish: () => setVoting(false),
        };

        if (entry.is_voted) {
            // Снимаем голос через DELETE-маршрут (wayfinder: entries.vote.destroy)
            router.delete(voteDestroy({ contest: contest.id, entry: entry.id }).url, options);
        } else {
            // Голосуем через POST-маршрут (wayfinder: entries.vote)
            router.post(vote({ contest: contest.id, entry: entry.id }).url, {}, options);
        }
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

    const handleOpenMedia = (entry: VotingActivity) => {
        setSelectedEntry(entry);
        setMediaDrawerOpen(true);
    };

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files) {
            setSelectedFiles(Array.from(e.target.files));
        }
    };

    const handleUploadMedia = async () => {
        if (!selectedEntry || selectedFiles.length === 0) return;

        setUploading(true);
        const formData = new FormData();
        selectedFiles.forEach(file => {
            formData.append('files[]', file);
        });

        try {
            await fetch(`/contest/${contest.id}/entries/${selectedEntry.id}/media`, {
                method: 'POST',
                body: formData,
            });
            message.success('Файлы загружены');
            setSelectedFiles([]);
            if (fileInputRef.current) {
                fileInputRef.current.value = '';
            }
            window.location.reload();
        } catch {
            message.error('Ошибка загрузки');
        } finally {
            setUploading(false);
        }
    };

    const getFileIcon = (type: string) => {
        switch (type) {
            case 'image':
                return <FileImageOutlined style={{ color: '#52c41a' }} />;
            case 'video':
                return <VideoCameraOutlined style={{ color: '#722ed1' }} />;
            case 'pdf':
                return <FilePdfOutlined style={{ color: '#ff4d4f' }} />;
            default:
                return <FileOutlined style={{ color: '#999' }} />;
        }
    };

    const getFileTypeLabel = (type: string) => {
        const labels: Record<string, string> = {
            image: 'Изображение',
            video: 'Видео',
            pdf: 'PDF',
            document: 'Документ',
        };
        return labels[type] || type;
    };

    const getFileTypeColor = (type: string) => {
        const colors: Record<string, string> = {
            image: 'green',
            video: 'purple',
            pdf: 'red',
            document: 'blue',
        };
        return colors[type] || 'default';
    };

    const formatFileSize = (bytes: number | null): string => {
        if (!bytes) return '—';
        const mb = bytes / (1024 * 1024);
        return `${mb.toFixed(2)} МБ`;
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
                                <Link key="back" href="/">
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
                                <Link key="back" href="/">
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
                    <Link href="/">
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
                                    <Tag>{contest.activities.filter((a) => a.type === "voting").length}</Tag>
                                </Space>
                            }
                        >
                            {contest.activities.filter((a) => a.type === "voting").length === 0 ? (
                                <Empty description="Работ пока нет" />
                            ) : (
                                <Row gutter={[16, 16]}>
                                    {contest.activities.map((activity) => {
                                        if (activity.type !== "voting") return null;
                                        // Show only the file marked as main (is_main: true); fallback to the first file if none is marked.
                                        const mainMedia =
                                            activity.media.find((m) => m.is_main) ??
                                            activity.media[0] ??
                                            null;
                                        const hasMedia = mainMedia !== null;

                                        return (
                                            <Col xs={24} sm={12} lg={8} key={activity.id}>
                                                <Card
                                                    hoverable
                                                    style={{ height: '100%' }}
                                                    onClick={() => handleOpenMedia(activity)}
                                                    cover={
                                                        hasMedia ? (
                                                            <div style={{ height: 200, background: '#f5f5f5', display: 'flex', alignItems: 'center', justifyContent: 'center', position: 'relative' }}>
                                                                {mainMedia.file_type === 'image' ? (
                                                                    <img
                                                                        src={mainMedia.file_url}
                                                                        alt={activity.title}
                                                                        style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                                                                    />
                                                                ) : (
                                                                    <div style={{ textAlign: 'center' }}>
                                                                        <span style={{ fontSize: 48 }}>{getFileIcon(mainMedia.file_type)}</span>
                                                                        <p style={{ margin: '8px 0 0', fontSize: 12, color: '#999' }}>
                                                                            {mainMedia.file_name}
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
                                                                        handlePreview(mainMedia);
                                                                    }}
                                                                >
                                                                    Просмотр
                                                                </Button>
                                                                <Button
                                                                    type="primary"
                                                                    size="small"
                                                                    icon={<PaperClipOutlined />}
                                                                    style={{ position: 'absolute', bottom: 8, left: 8 }}
                                                                    onClick={(e) => {
                                                                        e.preventDefault();
                                                                        e.stopPropagation();
                                                                        handleOpenMedia(activity);
                                                                    }}
                                                                >
                                                                    {activity.media.length} файлов
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

            {/* Media Drawer */}
            <Drawer
                title={
                    <Space>
                        <PaperClipOutlined />
                        <span>Работа: {activeEntry?.title ?? ""}</span>
                    </Space>
                }
                placement="right"
                onClose={() => setMediaDrawerOpen(false)}
                open={mediaDrawerOpen}
                width={500}
            >
                <Space direction="vertical" size="large" style={{ width: '100%' }}>
                    {/* Vote Section */}
                    {activeEntry && contest.is_active && (
                        <Card size="small">
                            <Space
                                style={{ width: '100%', justifyContent: 'space-between' }}
                                wrap
                            >
                                <Space>
                                    <TrophyOutlined style={{ color: '#faad14' }} />
                                    <Text strong>
                                        {activeEntry.votes_count}{' '}
                                        {activeEntry.votes_count === 1
                                            ? 'голос'
                                            : activeEntry.votes_count < 5
                                                ? 'голоса'
                                                : 'голосов'}
                                    </Text>
                                </Space>
                                <Button
                                    type={activeEntry.is_voted ? 'primary' : 'default'}
                                    danger={activeEntry.is_voted}
                                    icon={activeEntry.is_voted ? <HeartFilled /> : <HeartOutlined />}
                                    loading={voting}
                                    onClick={() => handleVote(activeEntry)}
                                >
                                    {activeEntry.is_voted ? 'Убрать голос' : 'Проголосовать'}
                                </Button>
                            </Space>
                        </Card>
                    )}

                    {/* Upload Section */}
                    <Card size="small" title="Загрузить файлы">
                        <input
                            ref={fileInputRef}
                            type="file"
                            multiple
                            accept="image/*,video/*,application/pdf,.doc,.docx,.xls,.xlsx"
                            style={{ display: 'none' }}
                            onChange={handleFileChange}
                        />
                        <Button
                            type="dashed"
                            onClick={() => fileInputRef.current?.click()}
                            style={{ width: '100%', marginBottom: 12 }}
                        >
                            <PlusOutlined /> Выбрать файлы
                        </Button>
                        {selectedFiles.length > 0 && (
                            <div style={{ marginBottom: 12 }}>
                                <Text style={{ display: 'block', marginBottom: 8 }}>
                                    Выбрано: {selectedFiles.length} файл(ов)
                                </Text>
                                <Space direction="vertical" size="small" style={{ width: '100%' }}>
                                    {selectedFiles.map((file, index) => (
                                        <Tag key={index} color="blue">
                                            {file.name} ({(file.size / 1024).toFixed(1)} КБ)
                                        </Tag>
                                    ))}
                                </Space>
                            </div>
                        )}
                        <Button
                            type="primary"
                            block
                            loading={uploading}
                            disabled={selectedFiles.length === 0}
                            onClick={handleUploadMedia}
                        >
                            Загрузить
                        </Button>
                    </Card>

                    {/* Existing Files */}
                    <Card size="small" title="Загруженные файлы">
                        {activeEntry?.media && activeEntry.media.length > 0 ? (
                            <Space direction="vertical" size="middle" style={{ width: '100%' }}>
                                {activeEntry.media.map((file) => (
                                    <Card
                                        key={file.id}
                                        size="small"
                                        hoverable
                                        styles={{ body: { padding: '8px 12px' } }}
                                    >
                                        <Space style={{ width: '100%', justifyContent: 'space-between' }}>
                                            <Space>
                                                {getFileIcon(file.file_type)}
                                                <Space direction="vertical" size={0}>
                                                    <Text strong style={{ fontSize: 13 }}>{file.file_name}</Text>
                                                    <Tag color={getFileTypeColor(file.file_type)}>
                                                        {getFileTypeLabel(file.file_type)}
                                                    </Tag>
                                                    <Text type="secondary" style={{ fontSize: 11 }}>
                                                        {formatFileSize(file.file_size)}
                                                    </Text>
                                                </Space>
                                            </Space>
                                            <Button
                                                type="text"
                                                size="small"
                                                icon={<EyeOutlined />}
                                                onClick={() => {
                                                    setPreviewFile(file);
                                                    setPreviewOpen(true);
                                                }}
                                            />
                                            <Button
                                                type="text"
                                                size="small"
                                                icon={<DownloadOutlined />}
                                                href={file.file_url}
                                                download
                                            />
                                        </Space>
                                    </Card>
                                ))}
                            </Space>
                        ) : (
                            <Empty description="Нет файлов" image={Empty.PRESENTED_IMAGE_SIMPLE} />
                        )}
                    </Card>
                </Space>
            </Drawer>
        </>
    );
}
