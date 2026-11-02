import {
    FileImageOutlined,
    FileOutlined,
    FilePdfOutlined,
    VideoCameraOutlined,
} from '@ant-design/icons';
import type { MediaItem } from '@/types/contest';

export const getFileIcon = (type: string): React.JSX.Element => {
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

export const getFileTypeLabel = (type: string): string => {
    const labels: Record<string, string> = {
        image: 'Изображение',
        video: 'Видео',
        pdf: 'PDF',
        document: 'Документ',
    };

    return labels[type] ?? type;
};

export const getFileTypeColor = (type: string): string => {
    const colors: Record<string, string> = {
        image: 'green',
        video: 'purple',
        pdf: 'red',
        document: 'blue',
    };

    return colors[type] ?? 'default';
};

export const formatFileSize = (bytes: number | null): string => {
    if (!bytes) {
        return '—';
    }

    const megabytes = bytes / (1024 * 1024);

    return `${megabytes.toFixed(2)} МБ`;
};

export const downloadMediaFile = (file: MediaItem): void => {
    const link = document.createElement('a');
    link.href = file.file_url;
    link.download = file.file_name;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
