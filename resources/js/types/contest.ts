export type MediaItem = {
    id: number;
    file_name: string;
    file_url: string;
    thumbnail_url: string | null;
    file_type: string;
    file_extension: string;
    file_size: number | null;
    is_main: boolean;
    created_at?: string | null;
};

export type QuizOption = {
    id: string;
    text: string;
    is_correct: boolean;
};

export type QuizActivity = {
    id: number;
    type: 'quiz';
    title: string;
    description: string | null;
    question: string | null;
    options: QuizOption[] | null;
    quizType: string | null;
    is_visible: boolean;
    is_answered: boolean;
    media: MediaItem[];
    created_at: string;
};

export type VotingActivity = {
    id: number;
    type: 'voting';
    title: string;
    description: string | null;
    author_name: string | null;
    author_department: string | null;
    votes_count: number;
    is_voted: boolean;
    is_viewed: boolean;
    media: MediaItem[];
    created_at: string;
};

export type ActivityItem = QuizActivity | VotingActivity;

export type LeaderboardItem = {
    id: number;
    title: string;
    votes_count: number;
    author_name: string | null;
    author_department: string | null;
};

export type ContestData = {
    id: number;
    title: string;
    type: string;
    status: string;
    slug?: string | null;
    description: string | null;
    start_at: string | null;
    end_at: string | null;
    is_active: boolean;
    is_time_active: boolean;
    is_archived: boolean;
    activities?: ActivityItem[] | null;
    leaderboard?: LeaderboardItem[];
    can_manage?: boolean;
    created_at: string;
};

export type ContestShowProps = {
    contest: ContestData;
    leaderboard: LeaderboardItem[];
};
