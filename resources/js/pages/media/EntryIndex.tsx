import { Head, Link, router, usePage } from "@inertiajs/react";
import { Button, Card, Empty, Image, Modal, Space, Table, Tag, Typography, message } from "antd";
import {
    PlusOutlined,
    DeleteOutlined,
    FileImageOutlined,
    FilePdfOutlined,
    FileExcelOutlined,
    FileWordOutlined,
    FileOutlined,
    VideoCameraOutlined,
    DownloadOutlined,
    EyeOutlined,
    ArrowLeftOutlined,
} from "@ant-design/icons";
import dayjs from "dayjs";
import { useState, useRef } from 'react';

const { Title, Text } = Typography;

type MediaItem = {
    id: number;
    file_name: string;
    file_url: string;
    file_type: string;
    file_extension: string;
    file_size: number | null;
    created_at: string;
};

type ContestItem = {
    id: number;
    title: string;
};

type EntryItem = {
    id: number;
    title: string;
};

type EntryMediaIndexProps = {
    contest: ContestItem;
    entry: EntryItem;
    media: MediaItem[];
};

const getFileIcon = (type: string, extension: string) => {
    switch (type) {
        case 'image':
            return <FileImageOutlined style={{ color: '#52c41a' }} />;
        case 'video':
            return <VideoCameraOutlined style={{ color: '#722ed1' }} />;
        case 'pdf':
            return <FilePdfOutlined style={{ color: '#ff4d4f' }} />;
        case 'document':
            if (extension === 'docx' || extension === 'doc') {
                return <FileWordOutlined style={{ color: '#1677ff' }} />;
            }
            if (extension === 'xlsx' || extension === 'xls') {
                return <FileExcelOutlined style={{ color: '#52c41a' }} />;
            }
            return <FileOutlined style={{ color: '#999' }} />;
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
        audio: 'Аудио',
    };
    return labels[type] || type;
};

const getFileTypeColor = (type: string) => {
    const colors: Record<string, string> = {
        image: 'green',
        video: 'purple',
        pdf: 'red',
        document: 'blue',
        audio: 'orange',
    };
    return colors[type] || 'default';
};

const formatFileSize = (bytes: number | null): string => {
    if (!bytes) return '—';
    const mb = bytes / (1024 * 1024);
    return `${mb.toFixed(2)} МБ`;
};

export default function EntryMediaIndex({ contest, entry, media }: EntryMediaIndexProps): React.JSX.Element {
    const { app } = usePage().props as { app: { max_upload_size: number } };
    const [previewOpen, setPreviewOpen] = useState(false);
    const [previewFile, setPreviewFile] = useState<MediaItem | null>(null);
    const [uploadModalOpen, setUploadModalOpen] = useState(false);
    const [uploading, setUploading] = useState(false);
    const [selectedFiles, setSelectedFiles] = useState<File[]>([]);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const maxUploadSizeMB = (app.max_upload_size / 1024).toFixed(0);

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files) {
            setSelectedFiles(Array.from(e.target.files));
        }
    };

    const handleUpload = async () => {
        if (selectedFiles.length === 0) {
            message.error('Выберите файлы для загрузки');
            return;
        }

        setUploading(true);
        const formData = new FormData();
        selectedFiles.forEach(file => {
            formData.append('files[]', file);
        });

        router.post(`/contest/${contest.id}/entries/${entry.id}/media`, formData, {
            preserveScroll: true,
            onSuccess: () => {
                message.success('Файлы успешно загружены');
                setUploadModalOpen(false);
                setSelectedFiles([]);
                if (fileInputRef.current) {
                    fileInputRef.current.value = '';
                }
            },
            onError: () => {
                message.error('Ошибка загрузки файлов');
            },
            onFinish: () => {
                setUploading(false);
            },
        });
    };

    const handleDelete = (id: number) => {
        if (confirm('Вы уверены, что хотите удалить этот файл?')) {
            router.delete(`/contest/${contest.id}/entries/${entry.id}/media/${id}`, {
                preserveScroll: true,
            });
        }
    };

    const handlePreview = (file: MediaItem) => {
        setPreviewFile(file);
        setPreviewOpen(true);
    };

    const columns = [
        {
            title: 'Файл',
            dataIndex: 'file_name',
            key: 'file_name',
            render: (name: string, record: MediaItem) => (
                <Space>
                    {getFileIcon(record.file_type, record.file_extension)}
                    <Text strong>{name}</Text>
                    <Tag color={getFileTypeColor(record.file_type)}>
                        {getFileTypeLabel(record.file_type)}
                    </Tag>
                </Space>
            ),
        },
        {
            title: 'Размер',
            dataIndex: 'file_size',
            key: 'file_size',
            render: (size: number | null) => formatFileSize(size),
            width: 120,
        },
        {
            title: 'Загружен',
            dataIndex: 'created_at',
            key: 'created_at',
            render: (date: string) => dayjs(date).format('DD.MM.YYYY HH:mm'),
            width: 150,
        },
        {
            title: 'Действия',
            key: 'actions',
            width: 200,
            render: (_: unknown, record: MediaItem) => (
                <Space>
                    <Button
                        type="text"
                        icon={<EyeOutlined />}
                        onClick={() => handlePreview(record)}
                    />
                    <Button
                        type="text"
                        icon={<DownloadOutlined />}
                        href={record.file_url}
                        download
                    />
                    <Button
                        type="text"
                        danger
                        icon={<DeleteOutlined />}
                        onClick={() => handleDelete(record.id)}
                    />
                </Space>
            ),
        },
    ];

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
                        <FileOutlined style={{ fontSize: 48, color: '#999' }} />
                        <p>Предпросмотр недоступен для этого типа файла</p>
                        <Button type="primary" href={previewFile.file_url} download>
                            Скачать файл
                        </Button>
                    </div>
                );
        }
    };

    return (
        <>
            <Head title={`Медиа: ${entry.title}`} />

            <div style={{ maxWidth: 1200, margin: '0 auto', padding: '24px 0' }}>
                <Space direction="vertical" size="large" style={{ width: '100%' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                        <div>
                            <Link href={`/contest/${contest.id}/entries`}>
                                <Button type="text" icon={<ArrowLeftOutlined />} style={{ float: 'left', marginRight: 12 }}>
                                    Назад
                                </Button>
                            </Link>
                            <Title level={2} style={{ margin: 0 }}>{contest.title}</Title>
                            <Text type="secondary">
                                Медиафайлы работы: <strong>{entry.title}</strong>
                            </Text>
                        </div>
                        <Button
                            type="primary"
                            icon={<PlusOutlined />}
                            onClick={() => setUploadModalOpen(true)}
                        >
                            Загрузить файлы
                        </Button>
                    </div>

                    <Card>
                        {media.length === 0 ? (
                            <Empty description="Нет загруженных файлов" />
                        ) : (
                            <Table
                                columns={columns}
                                dataSource={media}
                                rowKey="id"
                                pagination={{ pageSize: 10 }}
                            />
                        )}
                    </Card>
                </Space>
            </div>

            {/* Upload Modal */}
            <Modal
                title="Загрузка файлов"
                open={uploadModalOpen}
                onOk={handleUpload}
                onCancel={() => {
                    setUploadModalOpen(false);
                    setSelectedFiles([]);
                }}
                confirmLoading={uploading}
                okText="Загрузить"
                cancelText="Отмена"
            >
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
                    style={{ width: '100%', marginBottom: 16 }}
                >
                    <PlusOutlined /> Выберите файлы
                </Button>
                {selectedFiles.length > 0 && (
                    <div style={{ marginBottom: 12 }}>
                        <Text style={{ display: 'block', marginBottom: 8 }}>Выбрано файлов: {selectedFiles.length}</Text>
                        <Space direction="vertical" size="small" style={{ width: '100%' }}>
                            {selectedFiles.map((file, index) => (
                                <Tag key={index} color="blue">
                                    {file.name} ({(file.size / 1024).toFixed(1)} КБ)
                                </Tag>
                            ))}
                        </Space>
                    </div>
                )}
                <Text type="secondary">
                    Максимальный размер файла: {maxUploadSizeMB} МБ. Поддерживаемые форматы: изображения, видео, PDF, DOCX, XLSX.
                </Text>
            </Modal>

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
