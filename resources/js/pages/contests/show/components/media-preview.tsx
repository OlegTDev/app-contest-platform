import { Button, Image, Modal } from 'antd';
import type { MediaItem } from '@/types/contest';

type MediaPreviewProps = {
    file: MediaItem | null;
    open: boolean;
    onClose: () => void;
};

const renderPreview = (file: MediaItem | null): React.JSX.Element | null => {
    if (file === null) {
        return null;
    }

    switch (file.file_type) {
        case 'image':
            return (
                <Image
                    src={file.file_url}
                    style={{ maxWidth: '100%', maxHeight: '70vh' }}
                />
            );
        case 'video':
            return (
                <video
                    controls
                    style={{ maxWidth: '100%', maxHeight: '70vh' }}
                    src={file.file_url}
                >
                    Ваш браузер не поддерживает видео.
                </video>
            );
        case 'pdf':
            return (
                <iframe
                    src={file.file_url}
                    style={{ width: '100%', height: '70vh', border: 'none' }}
                    title="PDF Preview"
                />
            );
        default:
            return (
                <div style={{ textAlign: 'center', padding: '40px 0' }}>
                    <p>Предпросмотр недоступен для этого типа файла</p>
                    <Button type="primary" href={file.file_url} download>
                        Скачать файл
                    </Button>
                </div>
            );
    }
};

export default function MediaPreview({
    file,
    open,
    onClose,
}: MediaPreviewProps): React.JSX.Element {
    return (
        <Modal
            title={file?.file_name}
            open={open}
            footer={null}
            onCancel={onClose}
            destroyOnHidden
            width="80%"
            style={{ maxWidth: 1200 }}
        >
            {renderPreview(file)}
        </Modal>
    );
}
