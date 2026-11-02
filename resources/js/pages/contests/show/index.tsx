import { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { Button, Card, Result, Space } from 'antd';
import type {
    ContestShowProps,
    MediaItem,
    QuizActivity,
    VotingActivity,
} from '@/types/contest';
import ContestHeader, {
    CONTEST_STATUS_LABELS,
} from './components/contest-header';
import Leaderboard from './components/leaderboard';
import MediaPreview from './components/media-preview';
import { downloadMediaFile } from './components/file-icon';
import Quiz from './quiz';
import Voting from './voting';

export default function ContestShow({
    contest,
    leaderboard,
}: ContestShowProps): React.JSX.Element {
    const [previewOpen, setPreviewOpen] = useState(false);
    const [previewFile, setPreviewFile] = useState<MediaItem | null>(null);
    const [leaderboardDrawerOpen, setLeaderboardDrawerOpen] = useState(false);

    const activities = contest.activities ?? [];
    const quizActivities = activities.filter(
        (activity): activity is QuizActivity => activity.type === 'quiz',
    );
    const votingActivities = activities.filter(
        (activity): activity is VotingActivity => activity.type === 'voting',
    );

    const handleFileClick = (file: MediaItem): void => {
        if (file.file_type === 'video' || file.file_type === 'pdf') {
            setPreviewFile(file);
            setPreviewOpen(true);
            return;
        }

        downloadMediaFile(file);
    };

    const closePreview = (): void => {
        setPreviewOpen(false);
        setPreviewFile(null);
    };

    if (contest.status === 'closed' || contest.status === 'paused') {
        return (
            <>
                <Head title={contest.title} />
                <div
                    style={{
                        maxWidth: 800,
                        margin: '100px auto',
                        textAlign: 'center',
                    }}
                >
                    <Card>
                        <Result
                            status="warning"
                            title="Конкурс завершён"
                            subTitle={`Статус: ${
                                CONTEST_STATUS_LABELS[contest.status] ||
                                contest.status
                            }`}
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

            <div style={{ margin: '0 auto', padding: '24px 0' }}>
                <Space
                    orientation="vertical"
                    size="large"
                    style={{ width: '100%' }}
                >
                    <ContestHeader
                        title={contest.title}
                        status={contest.status}
                        type={contest.type}
                        is_active={contest.is_active}
                        description={contest.description}
                        start_at={contest.start_at}
                        end_at={contest.end_at}
                        created_at={contest.created_at}
                    />

                    {contest.type === 'quiz' ? (
                        <Quiz
                            activities={quizActivities}
                            contest={contest}
                            onFileClick={handleFileClick}
                        />
                    ) : (
                        <Voting
                            activities={votingActivities}
                            contest={contest}
                            onFileClick={handleFileClick}
                            onOpenLeaderboard={() =>
                                setLeaderboardDrawerOpen(true)
                            }
                        />
                    )}
                </Space>
            </div>

            <MediaPreview
                file={previewFile}
                open={previewOpen}
                onClose={closePreview}
            />

            <Leaderboard
                open={leaderboardDrawerOpen}
                onClose={() => setLeaderboardDrawerOpen(false)}
                leaderboard={leaderboard}
            />
        </>
    );
}
