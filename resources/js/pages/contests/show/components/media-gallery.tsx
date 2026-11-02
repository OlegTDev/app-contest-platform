import {
    Card,
    Col,
    Divider,
    Empty,
    Image,
    Row,
    Space,
    Tag,
    Typography,
} from 'antd';
import { DownloadOutlined } from '@ant-design/icons';
import type { MediaItem } from '@/types/contest';
import {
    formatFileSize,
    getFileIcon,
    getFileTypeColor,
    getFileTypeLabel,
} from './file-icon';

const { Text } = Typography;

type MediaGalleryProps = {
    media: MediaItem[];
    onFileClick: (file: MediaItem) => void;
};

export default function MediaGallery({
    media,
    onFileClick,
}: MediaGalleryProps): React.JSX.Element {
    const images = media.filter((file) => file.file_type === 'image');
    const otherFiles = media.filter((file) => file.file_type !== 'image');

    return (
        <Card size="small" title="Медиа">
            {media.length === 0 ? (
                <Empty
                    description="Нет файлов"
                    image={Empty.PRESENTED_IMAGE_SIMPLE}
                />
            ) : (
                <Space
                    orientation="vertical"
                    size="middle"
                    style={{ width: '100%' }}
                >
                    {images.length > 0 && (
                        <Image.PreviewGroup>
                            <Row gutter={[8, 8]}>
                                {images.map((file) => (
                                    <Col span={8} key={file.id}>
                                        <Image
                                            src={file.file_url}
                                            alt={file.file_name}
                                            width="100%"
                                            height={120}
                                            style={{
                                                objectFit: 'cover',
                                                borderRadius: 6,
                                                border: '1px solid #f0f0f0',
                                            }}
                                            preview={{ mask: null }}
                                        />
                                    </Col>
                                ))}
                            </Row>
                        </Image.PreviewGroup>
                    )}

                    {otherFiles.length > 0 && (
                        <Space
                            orientation="vertical"
                            size="middle"
                            style={{ width: '100%' }}
                        >
                            {images.length > 0 && (
                                <Divider style={{ margin: '4px 0' }} />
                            )}
                            {otherFiles.map((file) => (
                                <Card
                                    key={file.id}
                                    size="small"
                                    hoverable
                                    styles={{ body: { padding: '8px 12px' } }}
                                    onClick={() => onFileClick(file)}
                                >
                                    <Space
                                        style={{
                                            width: '100%',
                                            justifyContent: 'space-between',
                                        }}
                                    >
                                        <Space>
                                            {getFileIcon(file.file_type)}
                                            <Space
                                                orientation="vertical"
                                                size={0}
                                            >
                                                <Text
                                                    strong
                                                    style={{ fontSize: 13 }}
                                                >
                                                    {file.file_name}
                                                </Text>
                                                <Tag
                                                    color={getFileTypeColor(
                                                        file.file_type,
                                                    )}
                                                >
                                                    {getFileTypeLabel(
                                                        file.file_type,
                                                    )}
                                                </Tag>
                                                <Text
                                                    type="secondary"
                                                    style={{ fontSize: 11 }}
                                                >
                                                    {formatFileSize(
                                                        file.file_size,
                                                    )}
                                                </Text>
                                            </Space>
                                        </Space>
                                        <DownloadOutlined
                                            style={{ color: '#999' }}
                                        />
                                    </Space>
                                </Card>
                            ))}
                        </Space>
                    )}
                </Space>
            )}
        </Card>
    );
}
