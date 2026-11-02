import { Drawer, Empty, Space, Table, Tag, Typography } from 'antd';
import { TrophyOutlined } from '@ant-design/icons';
import type { LeaderboardItem } from '@/types/contest';

const { Text } = Typography;

type LeaderboardProps = {
    open: boolean;
    onClose: () => void;
    leaderboard: LeaderboardItem[];
};

export default function Leaderboard({
    open,
    onClose,
    leaderboard,
}: LeaderboardProps): React.JSX.Element {
    return (
        <Drawer
            title={
                <Space>
                    <TrophyOutlined style={{ color: '#faad14' }} />
                    <span>Результаты голосования</span>
                </Space>
            }
            placement="right"
            onClose={onClose}
            open={open}
            width={600}
        >
            {leaderboard.length === 0 ? (
                <Empty description="Голосов пока нет" />
            ) : (
                <Table
                    dataSource={leaderboard}
                    rowKey="id"
                    pagination={false}
                    size="small"
                    columns={[
                        {
                            title: '#',
                            key: 'rank',
                            width: 50,
                            render: (
                                _: unknown,
                                __: LeaderboardItem,
                                index: number,
                            ) => (
                                <Space>
                                    {index === 0 && (
                                        <span style={{ fontSize: 20 }}>🥇</span>
                                    )}
                                    {index === 1 && (
                                        <span style={{ fontSize: 20 }}>🥈</span>
                                    )}
                                    {index === 2 && (
                                        <span style={{ fontSize: 20 }}>🥉</span>
                                    )}
                                    {index > 2 && (
                                        <span style={{ color: '#999' }}>
                                            {index + 1}
                                        </span>
                                    )}
                                </Space>
                            ),
                        },
                        {
                            title: 'Участник',
                            dataIndex: 'title',
                            key: 'title',
                            width: 250,
                            sorter: false,
                            render: (
                                title: string,
                                record: LeaderboardItem,
                            ) => (
                                <Space orientation="vertical" size={0}>
                                    <Text
                                        strong
                                        style={{ wordBreak: 'break-word' }}
                                    >
                                        {title}
                                    </Text>
                                    {record.author_name && (
                                        <Text
                                            type="secondary"
                                            style={{
                                                fontSize: 12,
                                                wordBreak: 'break-word',
                                                display: 'block',
                                            }}
                                        >
                                            {record.author_name}
                                        </Text>
                                    )}
                                    {record.author_department && (
                                        <Text
                                            type="secondary"
                                            style={{
                                                fontSize: 12,
                                                wordBreak: 'break-word',
                                                display: 'block',
                                            }}
                                        >
                                            {record.author_department}
                                        </Text>
                                    )}
                                </Space>
                            ),
                        },
                        {
                            title: 'Голоса',
                            dataIndex: 'votes_count',
                            key: 'votes_count',
                            width: 100,
                            sorter: false,
                            render: (votes: number) => (
                                <Tag color="blue">
                                    {votes}{' '}
                                    {votes === 1
                                        ? 'голос'
                                        : votes < 5
                                          ? 'голоса'
                                          : 'голосов'}
                                </Tag>
                            ),
                        },
                    ]}
                />
            )}
        </Drawer>
    );
}
