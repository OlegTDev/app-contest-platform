import { Link } from '@inertiajs/react';
import { Button, Card, Descriptions, Space, Tag, Typography } from 'antd';
import { ArrowLeftOutlined } from '@ant-design/icons';
import dayjs from 'dayjs';

const { Title, Paragraph } = Typography;

export const CONTEST_STATUS_LABELS: Record<string, string> = {
    draft: 'Черновик',
    published: 'Опубликован',
    paused: 'На паузе',
    closed: 'Закрыт',
    archived: 'Архив',
};

type ContestHeaderProps = {
    title: string;
    status: string;
    type: string;
    is_active: boolean;
    description: string | null;
    start_at: string | null;
    end_at: string | null;
    created_at: string;
};

export default function ContestHeader({
    title,
    status,
    type,
    is_active,
    description,
    start_at,
    end_at,
    created_at,
}: ContestHeaderProps): React.JSX.Element {
    return (
        <>
            <Link href="/">
                <Button type="text" icon={<ArrowLeftOutlined />}>
                    К конкурсам
                </Button>
            </Link>

            <div>
                <Space>
                    <Title level={2} style={{ margin: 0 }}>
                        {title}
                    </Title>
                    <Tag color={is_active ? 'green' : 'default'}>
                        {is_active
                            ? '● Активен'
                            : CONTEST_STATUS_LABELS[status] || status}
                    </Tag>
                    <Tag color={type === 'quiz' ? 'blue' : 'green'}>
                        {type === 'quiz' ? 'Викторина' : 'Голосование'}
                    </Tag>
                </Space>
                <Paragraph type="secondary" style={{ marginTop: 8 }}>
                    Создан: {dayjs(created_at).format('DD.MM.YYYY HH:mm')}
                </Paragraph>
            </div>

            {description && (
                <Card>
                    <Paragraph>{description}</Paragraph>
                </Card>
            )}

            {(start_at || end_at) && (
                <Card title="Период голосования">
                    <Descriptions bordered column={1} size="small">
                        <Descriptions.Item label="Начало">
                            {start_at
                                ? dayjs(start_at).format('DD.MM.YYYY HH:mm')
                                : 'Не указано'}
                        </Descriptions.Item>
                        <Descriptions.Item label="Окончание">
                            {end_at
                                ? dayjs(end_at).format('DD.MM.YYYY HH:mm')
                                : 'Не указано'}
                        </Descriptions.Item>
                    </Descriptions>
                </Card>
            )}
        </>
    );
}
