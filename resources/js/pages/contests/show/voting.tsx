import { useState } from 'react';
import { router } from '@inertiajs/react';
import {
    Button,
    Card,
    Col,
    Drawer,
    Empty,
    Row,
    Space,
    Tag,
    Typography,
    message,
} from 'antd';
import {
    ApartmentOutlined,
    EyeOutlined,
    HeartFilled,
    HeartOutlined,
    PaperClipOutlined,
    ThunderboltOutlined,
    TrophyOutlined,
    UserOutlined,
} from '@ant-design/icons';
import { vote as voteStore } from '@/routes/entries';
import { destroy as voteDestroy } from '@/routes/entries/vote';
import { view as markEntryViewed } from '@/routes/contests/public';
import type { ContestData, MediaItem, VotingActivity } from '@/types/contest';
import { getFileIcon } from './components/file-icon';
import MediaGallery from './components/media-gallery';

const { Title, Text, Paragraph } = Typography;

type VotingProps = {
    activities: VotingActivity[];
    contest: ContestData;
    onFileClick: (file: MediaItem) => void;
    onOpenLeaderboard: () => void;
};

const pluralizeVotes = (votes: number): string => {
    if (votes === 1) {
        return 'голос';
    }

    return votes < 5 ? 'голоса' : 'голосов';
};

export default function Voting({
    activities,
    contest,
    onFileClick,
    onOpenLeaderboard,
}: VotingProps): React.JSX.Element {
    const [selectedEntry, setSelectedEntry] = useState<VotingActivity | null>(
        null,
    );
    const [voting, setVoting] = useState(false);
    const [mediaDrawerOpen, setMediaDrawerOpen] = useState(false);

    const activeEntry =
        activities.find((entry) => entry.id === selectedEntry?.id) ?? null;

    const handleVote = (entry: VotingActivity): void => {
        if (!contest.is_time_active && !contest.is_archived) {
            message.warning('Период голосования завершён');
            return;
        }

        setVoting(true);

        const options = {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                setVoting(false);
                message.success(
                    entry.is_voted ? 'Голос удалён' : 'Голос засчитан!',
                );
            },
            onError: () => {
                setVoting(false);
                message.error('Не удалось проголосовать');
            },
            onFinish: () => setVoting(false),
        };

        const routeArguments = { contest: contest.id, entry: entry.id };

        if (entry.is_voted) {
            router.delete(voteDestroy(routeArguments).url, options);
        } else {
            router.post(voteStore(routeArguments).url, {}, options);
        }
    };

    const handleOpenMedia = (entry: VotingActivity): void => {
        setSelectedEntry(entry);
        setMediaDrawerOpen(true);

        if (!entry.is_viewed) {
            router.post(
                markEntryViewed({ contest: contest.id, entry: entry.id }).url,
                {},
                { preserveScroll: true, preserveState: true },
            );
        }
    };

    return (
        <>
            <Card
                title={
                    <Space>
                        <ThunderboltOutlined style={{ color: '#52c41a' }} />
                        <span>Работы</span>
                        <Tag>{activities.length}</Tag>
                    </Space>
                }
                extra={
                    activities.length > 0 ? (
                        <Button
                            type="primary"
                            icon={<TrophyOutlined />}
                            onClick={onOpenLeaderboard}
                        >
                            Результаты голосования
                        </Button>
                    ) : null
                }
            >
                {activities.length === 0 ? (
                    <Empty description="Работ пока нет" />
                ) : (
                    <Row gutter={[16, 16]}>
                        {activities.map((activity) => {
                            const mainMedia =
                                activity.media.find((item) => item.is_main) ??
                                activity.media[0] ??
                                null;
                            const hasMedia = mainMedia !== null;

                            return (
                                <Col xs={24} sm={12} lg={8} key={activity.id}>
                                    <Card
                                        hoverable
                                        style={{ height: '100%' }}
                                        onClick={() =>
                                            handleOpenMedia(activity)
                                        }
                                        cover={
                                            hasMedia ? (
                                                <div
                                                    style={{
                                                        height: 200,
                                                        background: '#f5f5f5',
                                                        display: 'flex',
                                                        alignItems: 'center',
                                                        justifyContent:
                                                            'center',
                                                        position: 'relative',
                                                    }}
                                                >
                                                    {mainMedia.file_type ===
                                                    'image' ? (
                                                        <img
                                                            src={
                                                                mainMedia.thumbnail_url ??
                                                                mainMedia.file_url
                                                            }
                                                            alt={activity.title}
                                                            style={{
                                                                width: '100%',
                                                                height: '100%',
                                                                objectFit:
                                                                    'contain',
                                                            }}
                                                        />
                                                    ) : (
                                                        <div
                                                            style={{
                                                                textAlign:
                                                                    'center',
                                                            }}
                                                        >
                                                            <span
                                                                style={{
                                                                    fontSize: 48,
                                                                }}
                                                            >
                                                                {getFileIcon(
                                                                    mainMedia.file_type,
                                                                )}
                                                            </span>
                                                            <p
                                                                style={{
                                                                    margin: '8px 0 0',
                                                                    fontSize: 12,
                                                                    color: '#999',
                                                                }}
                                                            >
                                                                {
                                                                    mainMedia.file_name
                                                                }
                                                            </p>
                                                        </div>
                                                    )}
                                                </div>
                                            ) : (
                                                <div
                                                    style={{
                                                        height: 200,
                                                        background: '#f5f5f5',
                                                        display: 'flex',
                                                        alignItems: 'center',
                                                        justifyContent:
                                                            'center',
                                                        color: '#999',
                                                    }}
                                                >
                                                    Нет медиа
                                                </div>
                                            )
                                        }
                                    >
                                        <Space
                                            orientation="vertical"
                                            style={{ width: '100%' }}
                                        >
                                            <Title
                                                level={5}
                                                style={{ marginBottom: 4 }}
                                            >
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
                                                <Tag
                                                    variant="filled"
                                                    style={{
                                                        marginBottom: 4,
                                                        whiteSpace: 'normal',
                                                        wordBreak: 'break-word',
                                                        width: '100%',
                                                    }}
                                                >
                                                    {activity.author_name}
                                                </Tag>
                                            )}
                                            {activity.author_department && (
                                                <Tag
                                                    variant="filled"
                                                    style={{
                                                        whiteSpace: 'normal',
                                                        wordBreak: 'break-word',
                                                        width: '100%',
                                                    }}
                                                >
                                                    {activity.author_department}
                                                </Tag>
                                            )}
                                            {hasMedia && (
                                                <Tag
                                                    color="blue"
                                                    style={{ marginBottom: 8 }}
                                                >
                                                    📎 {activity.media.length}{' '}
                                                    файл(ов)
                                                </Tag>
                                            )}
                                            <Space
                                                style={{
                                                    marginTop: 'auto',
                                                    width: '100%',
                                                    justifyContent:
                                                        'space-between',
                                                }}
                                            >
                                                <Space>
                                                    <TrophyOutlined
                                                        style={{
                                                            color: '#faad14',
                                                        }}
                                                    />
                                                    <Tag color="blue">
                                                        {activity.votes_count}{' '}
                                                        {pluralizeVotes(
                                                            activity.votes_count,
                                                        )}
                                                    </Tag>
                                                </Space>
                                                {contest.is_active &&
                                                    activity.is_voted && (
                                                        <Tag
                                                            variant="outlined"
                                                            color="volcano"
                                                            icon={
                                                                <HeartFilled />
                                                            }
                                                        >
                                                            Вы проголосовали
                                                        </Tag>
                                                    )}
                                                {contest.is_archived &&
                                                    activity.is_voted && (
                                                        <Tag
                                                            variant="outlined"
                                                            color="default"
                                                            icon={
                                                                <HeartFilled />
                                                            }
                                                        >
                                                            Голос в архиве
                                                        </Tag>
                                                    )}
                                            </Space>
                                            {activity.is_viewed && (
                                                <Text
                                                    type="secondary"
                                                    style={{
                                                        fontSize: 11,
                                                        display: 'block',
                                                        marginTop: 4,
                                                    }}
                                                >
                                                    <EyeOutlined /> Просмотрено
                                                </Text>
                                            )}
                                        </Space>
                                    </Card>
                                </Col>
                            );
                        })}
                    </Row>
                )}
            </Card>

            <Drawer
                title={
                    <Space>
                        <PaperClipOutlined />
                        <span>Работа: {activeEntry?.title ?? ''}</span>
                    </Space>
                }
                placement="right"
                onClose={() => setMediaDrawerOpen(false)}
                open={mediaDrawerOpen}
                width={750}
            >
                <Space
                    orientation="vertical"
                    size="large"
                    style={{ width: '100%' }}
                >
                    {activeEntry &&
                        (contest.is_time_active || contest.is_archived) && (
                            <Card size="small">
                                <Space
                                    style={{
                                        width: '100%',
                                        justifyContent: 'space-between',
                                    }}
                                    wrap
                                >
                                    <Space>
                                        <TrophyOutlined
                                            style={{ color: '#faad14' }}
                                        />
                                        <Text strong>
                                            {activeEntry.votes_count}{' '}
                                            {pluralizeVotes(
                                                activeEntry.votes_count,
                                            )}
                                        </Text>
                                    </Space>
                                    <Button
                                        type={
                                            activeEntry.is_voted
                                                ? 'primary'
                                                : 'default'
                                        }
                                        danger={activeEntry.is_voted}
                                        icon={
                                            activeEntry.is_voted ? (
                                                <HeartFilled />
                                            ) : (
                                                <HeartOutlined />
                                            )
                                        }
                                        loading={voting}
                                        disabled={
                                            !contest.is_time_active &&
                                            !contest.is_archived
                                        }
                                        onClick={() => handleVote(activeEntry)}
                                    >
                                        {activeEntry.is_voted
                                            ? 'Убрать голос'
                                            : 'Проголосовать'}
                                    </Button>
                                </Space>
                            </Card>
                        )}

                    {(activeEntry?.author_name ||
                        activeEntry?.author_department) && (
                        <Card size="small">
                            <Space
                                orientation="vertical"
                                size={0}
                                style={{ width: '100%' }}
                            >
                                {activeEntry.author_name && (
                                    <Space>
                                        <UserOutlined />
                                        <Text>{activeEntry.author_name}</Text>
                                    </Space>
                                )}
                                {activeEntry.author_department && (
                                    <Space>
                                        <ApartmentOutlined />
                                        <Text type="secondary">
                                            {activeEntry.author_department}
                                        </Text>
                                    </Space>
                                )}
                            </Space>
                        </Card>
                    )}

                    {activeEntry?.description && (
                        <Card size="small" title="Описание">
                            <Paragraph>{activeEntry.description}</Paragraph>
                        </Card>
                    )}

                    <MediaGallery
                        media={activeEntry?.media ?? []}
                        onFileClick={onFileClick}
                    />
                </Space>
            </Drawer>
        </>
    );
}
