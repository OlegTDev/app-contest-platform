<?php

namespace Database\Seeders;

use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\ContestVote;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContestVotingSeeder extends Seeder
{
    /**
     * @var string[]
     */
    private const RUSSIAN_FIRST_NAMES = [
        'Александр', 'Дмитрий', 'Максим', 'Сергей', 'Андрей',
        'Алексей', 'Артём', 'Иван', 'Михаил', 'Даниил',
        'Кирилл', 'Роман', 'Никита', 'Егор', 'Матвей',
        'Мария', 'Анна', 'Елена', 'Ольга', 'Наталья',
        'Ирина', 'Светлана', 'Татьяна', 'Юлия', 'Екатерина',
    ];

    /**
     * @var string[]
     */
    private const RUSSIAN_LAST_NAMES = [
        'Иванов', 'Петров', 'Сидоров', 'Козлов', 'Новиков',
        'Морозов', 'Волков', 'Соколов', 'Лебедев', 'Кузнецов',
        'Попов', 'Васильев', 'Семёнов', 'Егоров', 'Павлов',
        'Смирнова', 'Кузнецова', 'Попова', 'Васильева', 'Соколова',
    ];

    /**
     * @var string[]
     */
    private const DEPARTMENTS = [
        'Отдел разработки', 'Отдел дизайна', 'Отдел аналитики',
        'Отдел тестирования', 'Отдел DevOps', 'Отдел продуктовой аналитики',
        'Юридический отдел', 'Отдел маркетинга', 'HR-отдел',
        'Финансовый отдел', 'Отдел инфраструктуры', 'Отдел машинного обучения',
    ];

    /**
     * @var string[]
     */
    private const CONTEST_TITLES = [
        'Лучший проект года',
        'Инновация года',
        'Цифровая трансформация',
        'Лучший IT-продукт',
        'Командный дух',
        'Открытие года',
        'Лучший кейс внедрения',
        'Прорыв года',
    ];

    /**
     * @var string[]
     */
    private const ENTRY_TITLES = [
        'Автоматизация процессов',
        'Умная аналитика данных',
        'Платформа для сотрудников',
        'Система мониторинга',
        'Мобильное приложение',
        'Интеграция сервисов',
        'CRM нового поколения',
        'Чат-бот поддержки',
        'Система рекомендаций',
        'Дашборд эффективности',
        'Платформа обучения',
        'Система управления задачами',
        'Сервис обратной связи',
        'Панель администратора',
        'Автоматическое тестирование',
        'Система документооборота',
        'Платформа для менторства',
        'Система оценки компетенций',
        'Виртуальный ассистент',
        'Система прогнозирования',
    ];

    /**
     * @var string[]
     */
    private const ENTRY_DESCRIPTIONS = [
        'Проект направлен на оптимизацию рабочих процессов и повышение эффективности команды за счёт внедрения современных технологий.',
        'Решение позволяет автоматизировать рутинные задачи и сократить время на их выполнение в несколько раз.',
        'Проект объединяет несколько сервисов в единую экосистему для удобства пользователей и повышения продуктивности.',
        'Система обеспечивает мониторинг ключевых метрик в реальном времени и формирует отчёты для руководства.',
        'Мобильное приложение предоставляет удобный доступ к корпоративным сервисам из любой точки мира.',
        'Проект решает задачу бесшовной интеграции разрозненных систем компании в единое информационное пространство.',
        'Решение помогает анализировать большие объёмы данных и принимать обоснованные управленческие решения.',
        'Проект направлен на улучшение клиентского опыта за счёт внедрения интеллектуальных технологий поддержки.',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $numberOfUsers = 20;
        $numberOfContests = 3;
        $entriesPerContest = 15;
        $maxVotesPerEntry = 12;

        DB::transaction(function () use ($numberOfUsers, $numberOfContests, $entriesPerContest, $maxVotesPerEntry) {
            // Создаём пользователей
            $users = $this->createUsers($numberOfUsers);

            // Создаём конкурсы
            $contests = $this->createContests($users, $numberOfContests);

            // Создаём работы и голоса для каждого конкурса
            foreach ($contests as $contest) {
                $this->createEntriesAndVotes($contest, $users, $entriesPerContest, $maxVotesPerEntry);
            }
        });
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function createUsers(int $count): \Illuminate\Support\Collection
    {
        return User::factory()->count($count)->create()->map(function (User $user, int $index): User {
            $firstName = self::RUSSIAN_FIRST_NAMES[$index % count(self::RUSSIAN_FIRST_NAMES)];
            $lastName = self::RUSSIAN_LAST_NAMES[$index % count(self::RUSSIAN_LAST_NAMES)];
            $user->name = "$firstName $lastName";
            $user->email = strtolower($firstName) . '.' . strtolower($lastName) . '@company.ru';
            $user->save();

            return $user;
        });
    }

    /**
     * @param \Illuminate\Support\Collection<int, User> $users
     * @return \Illuminate\Support\Collection<int, Contest>
     */
    private function createContests(\Illuminate\Support\Collection $users, int $count): \Illuminate\Support\Collection
    {
        $contests = collect();

        for ($i = 0; $i < $count; $i++) {
            $contest = Contest::create([
                'user_id' => $users->random()->id,
                'title' => self::CONTEST_TITLES[$i % count(self::CONTEST_TITLES)],
                'type' => 'voting',
                'status' => 'published',
                'description' => self::ENTRY_DESCRIPTIONS[$i % count(self::ENTRY_DESCRIPTIONS)],
                'start_at' => now()->subDays(rand(1, 5)),
                'end_at' => now()->addDays(rand(10, 30)),
                'project_schema' => null,
            ]);

            $contests->push($contest);
        }

        return $contests;
    }

    /**
     * @param Contest $contest
     * @param \Illuminate\Support\Collection<int, User> $users
     * @return void
     */
    private function createEntriesAndVotes(Contest $contest, \Illuminate\Support\Collection $users, int $entriesCount, int $maxVotes): void
    {
        $entryIds = [];
        $userIds = $users->pluck('id')->toArray();

        // Создаём работы
        for ($i = 0; $i < $entriesCount; $i++) {
            $authorId = $userIds[array_rand($userIds)];
            $firstName = self::RUSSIAN_FIRST_NAMES[$authorId % count(self::RUSSIAN_FIRST_NAMES)];
            $lastName = self::RUSSIAN_LAST_NAMES[$authorId % count(self::RUSSIAN_LAST_NAMES)];
            $department = self::DEPARTMENTS[array_rand(self::DEPARTMENTS)];

            $entry = ContestEntry::create([
                'contest_id' => $contest->id,
                'user_id' => $authorId,
                'title' => self::ENTRY_TITLES[$i % count(self::ENTRY_TITLES)],
                'description' => self::ENTRY_DESCRIPTIONS[$i % count(self::ENTRY_DESCRIPTIONS)],
                'author_name' => "$firstName $lastName",
                'author_department' => $department,
                'fields_data' => null,
                'votes_count' => 0,
            ]);

            $entryIds[] = $entry->id;
        }

        // Создаём голоса
        foreach ($entryIds as $entryId) {
            $numVoters = rand(3, $maxVotes);
            $availableVoters = $userIds;
            shuffle($availableVoters);

            foreach ($availableVoters as $voterId) {
                // Автор не голосует за свою работу
                $entry = ContestEntry::find($entryId);
                if ($entry && $entry->user_id === $voterId) {
                    continue;
                }

                // Проверка на дубликаты
                $exists = ContestVote::where('contest_id', $contest->id)
                    ->where('entry_id', $entryId)
                    ->where('user_id', $voterId)
                    ->exists();

                if (! $exists) {
                    ContestVote::create([
                        'contest_id' => $contest->id,
                        'entry_id' => $entryId,
                        'user_id' => $voterId,
                    ]);
                }

                if (count($availableVoters) >= $numVoters) {
                    break;
                }
            }
        }

        // Обновляем счётчик голосов
        foreach ($entryIds as $entryId) {
            ContestEntry::where('id', $entryId)->update([
                'votes_count' => ContestVote::where('entry_id', $entryId)->count(),
            ]);
        }
    }
}
