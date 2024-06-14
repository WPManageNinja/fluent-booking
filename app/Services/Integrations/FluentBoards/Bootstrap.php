<?php

namespace FluentBooking\App\Services\Integrations\FluentBoards;

use FluentBooking\App\App;
use FluentBoards\App\Models\Label;
use FluentBoards\App\Models\Stage;
use FluentBoards\App\Models\Task;
use FluentBoards\App\Models\Board;
use FluentBoards\App\Models\User;
use FluentBoards\App\Services\Constant;
use FluentBoards\App\Services\NotificationService;
use FluentBoards\App\Services\TaskService;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\App\Http\Controllers\IntegrationManagerController;

class Bootstrap extends IntegrationManagerController
{
    public $hasGlobalMenu = false;

    public $disableGlobalSettings = 'yes';

    public function __construct()
    {
        parent::__construct(
            __('FluentBoards', 'fluent-booking-pro'),
            'fluentboards',
            'fluent_booking_fluentboards_configurations',
            'fluentboards_feeds',
            10
        );

        $this->logo = App::getInstance('url.assets') . 'images/fluentboards.png';

        $this->description = __('Connect FluentBoards with Fluent Booking and create tasks with booking fields.', 'fluent-booking-pro');

        $this->registerAdminHooks();
    }

    public function pushIntegration($integrations, $calendarEventId)
    {
        $integrations[$this->integrationKey] = [
            'title'                 => $this->title . ' ' . __('Integration', 'fluent-booking-pro'),
            'logo'                  => $this->logo,
            'is_active'             => $this->isConfigured(),
            'configure_title'       => __('Configuration required!', 'fluent-booking-pro'),
            'global_configure_url'  => '#',
            'configure_message'     => __('FluentBoards is not configured yet! Please configure your FluentBoards api first', 'fluent-booking-pro'),
            'configure_button_text' => __('Set FluentBoards', 'fluent-booking-pro'),
        ];

        return $integrations;
    }

    public function isConfigured()
    {
        return true;
    }

    public function isEnabled()
    {
        return true;
    }

    public function getIntegrationDefaults($settings, $calendarEventId)
    {
        return [
            'name'         => '',
            'board_config' => [
                'board_id'   => '',
                'stage_id'   => '',
                'label_ids'  => '',
                'member_ids' => [],
                'priority'   => ''
            ],
            'task_title'   => '',
            'author_name'  => '',
            'email'        => '',
            'description'  => '',
            'position'     => 'bottom',
            'due_at_days'  => 1,
            'conditionals' => [
                'conditions' => [],
                'status'     => false,
                'type'       => 'all'
            ],
            'enabled'      => true
        ];
    }

    public function getSettingsFields($settings, $calendarEventId)
    {
        $fields = [
            [
                'key'         => 'name',
                'label'       => __('Feed Name', 'fluent-booking-pro'),
                'required'    => true,
                'placeholder' => __('Your Feed Name', 'fluent-booking-pro'),
                'component'   => 'text',
            ],
            [
                'key'            => 'board_config',
                'label'          => 'Fluent Boards Configuration',
                'required'       => true,
                'component'      => 'chained_select',
                'primary_key'    => 'board_id',
                'fields_options' => [
                    'board_id'    => [],
                    'stage_id'    => [],
                    'label_ids'   => [],
                    'member_ids'  => [],
                    'priority'    => []
                ],
                'options_labels' => [
                    'board_id'       => [
                        'label'       => 'Select Board',
                        'type'        => 'select',
                        'placeholder' => 'Select Board'
                    ],
                    'stage_id'       => [
                        'label'       => 'Select Stage',
                        'type'        => 'select',
                        'placeholder' => 'Select Stage'
                    ],
                    'label_ids' => [
                        'label'       => 'Select Labels',
                        'type'        => 'multi-select',
                        'placeholder' => 'Select Labels'
                    ],
                    'member_ids'     => [
                        'label'       => 'Select Assignees',
                        'type'        => 'multi-select',
                        'placeholder' => 'Select Assignees'
                    ],
                    'priority'       => [
                        'label'       => 'Select Priority',
                        'type'        => 'select',
                        'placeholder' => 'Priority'
                    ]
                ]
            ],
            [
                'key'         => 'task_title',
                'label'       => 'Task Title',
                'required'    => true,
                'placeholder' => 'Task Title',
                'component'   => 'value_text'
            ],
            [
                'key'         => 'description',
                'label'       => 'Description',
                'required'    => false,
                'placeholder' => 'Describe your task',
                'component'   => 'wp_editor',
            ],
            [
                'key'         => 'author_name',
                'label'       => 'Submitter Name',
                'required'    => true,
                'placeholder' => 'Submitter Name',
                'component'   => 'value_text'
            ],
            [
                'key'         => 'email',
                'label'       => 'Submitter Email',
                'required'    => true,
                'placeholder' => 'Submitter Email',
                'component'   => 'value_text'
            ],
            [
                'key'       => 'due_at_days',
                'label'     => 'Due Date',
                'tips'      => 'Days after booking scheduled, values less than zero will set due date to null.',
                'component' => 'number'
            ],
            [
                'key'         => 'position',
                'label'       => 'Task Position',
                'required'    => true,
                'placeholder' => 'Position',
                'component'   => 'radio_choice',
                'options'     => [
                    'bottom' => 'Bottom',
                    'top'    => 'Top'
                ]
            ],
            [
                'key'          => 'conditionals',
                'label'        => 'Conditional Logics',
                'tips'         => 'Allow integration conditionally based on your submission values',
                'component'    => 'conditional_block'
            ],
            [
                'require_list'   => false,
                'required'       => true,
                'key'            => 'event_trigger',
                'options'        => $this->getEventTriggerOptions(),
                'tips'           => __('Select in which booking stage you want to trigger this feed', 'fluent-booking-pro'),
                'label'          => __('Event Trigger', 'fluent-booking-pro'),
                'component'      => 'checkbox-multiple-text',
                'checkbox_label' => __('Event Trigger For This Feed', 'fluent-booking-pro'),
            ],
            [
                'require_list'   => false,
                'key'            => 'enabled',
                'label'          => __('Status', 'fluent-booking-pro'),
                'component'      => 'checkbox-single',
                'checkbox_label' => __('Enable This feed', 'fluent-booking-pro'),
            ]
        ];

        return [
            'fields'              => $fields,
            'button_require_list' => false,
            'integration_title'   => $this->title,
        ];
    }

    public function getMergeFields($list, $listId, $slotId)
    {
        return [];
    }

    public function getEventTriggerOptions()
    {
        return [
            'after_booking_scheduled'    => __('Booking Confirmed', 'fluent-booking-pro'),
            'booking_schedule_completed' => __('Booking Completed', 'fluent-booking-pro'),
            'booking_schedule_cancelled' => __('Booking Cancelled', 'fluent-booking-pro'),
        ];
    }
    
    public function getConfigFieldOptions($settings, $calendarEventId)
    {
        $boardId = Arr::get($settings, 'board_config.board_id');

        $data = [
            'board_id'   => $this->getBoards(),
            'stage_id'   => $boardId ? $this->getStages($boardId) : [],
            'label_ids'  => $boardId ? $this->getBoardLabels($boardId) : [],
            'member_ids' => $boardId ? $this->getBoardMembers($boardId) : [],
            'priority'   => $this->getBoardPriorities()
        ];

        $data = apply_filters('fluent_booking/fluent_board_config_field_options', $data, $boardId);

        return $data;
    }

    private function getBoards()
    {
        $boards = Board::whereNull('archived_at')
            ->select('id', 'title')
            ->get();

        $formattedBoards = $boards->mapWithKeys(function ($board) {
            return [$board->id => $board->title];
        })->toArray();

        return $formattedBoards;
    }

    private function getStages($boardId)
    {
        $stages = Stage::where('board_id', $boardId)
            ->whereNull('archived_at')
            ->select('id', 'title')
            ->get();

        $formattedStages = $stages->mapWithKeys(function ($stage) {
            return [$stage->id => $stage->title];
        })->toArray();

        return $formattedStages;
    }

    private function getBoardLabels($boardId)
    {
        $labels = Label::where('board_id', $boardId)
            ->whereNull('archived_at')
            ->orderBy('position', 'asc')
            ->get(['id', 'title', 'slug']);

        $formattedLabels = $labels->mapWithKeys(function ($label) {
            return [$label->id => $label->title ?? $label->slug];
        })->toArray();

        return $formattedLabels;
    }

    private function getBoardMembers($boardId)
    {
        $board = Board::with(['users'])->findOrFail($boardId);

        $formattedBoardUsers = $board->users->mapWithKeys(function ($user) {
            return [$user->ID => "{$user->user_login} ({$user->user_email})"];
        })->toArray();

        return $formattedBoardUsers;
    }

    private function getBoardPriorities()
    {
        return [
            'low'    => 'Low',
            'normal' => 'Normal',
            'high'   => 'High'
        ];
    }

    private function getLastPositionOfStageTask($boardId, $stageId)
    {
        $lastPosition = Task::query()
            ->where('board_id', $boardId)
            ->where('parent_id', null)
            ->where('stage_id', $stageId)
            ->whereNull('archived_at')
            ->orderBy('position', 'desc')
            ->pluck('position')
            ->first();

        return $lastPosition + 1;
    }

    private function dueDateConvertion($due_time, $unit)
    {
        if ($due_time > 0) {
            $currentTime = current_time('mysql');
            $readyString = '+' . $due_time . ' ' . $unit;
            return date('Y-m-d H:i:s', strtotime($readyString, strtotime($currentTime)));
        }
        return null;
    }

    public function notify($feed, $booking, $calendarEvent)
    {
        $feedData = $feed['processedValues'];
        $data = Arr::only($feedData, ['task_title', 'description', 'board_config', 'email', 'position', 'due_at_days']);

        $boardId     = intval(Arr::get($data, 'board_config.board_id'));
        $stageId     = intval(Arr::get($data, 'board_config.stage_id'));
        $priority    = sanitize_text_field(Arr::get($data, 'board_config.priority'));
        $assignees   = array_map('intval', Arr::get($data, 'board_config.member_ids', []));
        $boardLabels = array_map('intval', Arr::get($data, 'board_config.label_ids', []));
        $taskTitle   = sanitize_text_field(Arr::get($data, 'task_title'));
        $description = wp_kses_post(Arr::get($data, 'description'));
        $position    = sanitize_text_field(Arr::get($data, 'position'));
        $dueAtDays   = sanitize_text_field(Arr::get($data, 'due_at_days'));
        $authorName  = sanitize_text_field(Arr::get($data, 'author_name'));
        $authorEmail = sanitize_email(Arr::get($data, 'email'));

        if (!$booking->id || !$boardId || !$stageId || !$taskTitle) {
            return false;
        }

        $board = Board::find($boardId);
        if (!$board) {
            return false;
        }

        $data = [
            'title'          => $taskTitle,
            'board_id'       => $boardId,
            'stage_id'       => $stageId,
            'priority'       => $priority,
            'description'    => $description,
            'position'       => $this->getLastPositionOfStageTask($boardId, $stageId),
            'due_at'         => $this->dueDateConvertion($dueAtDays, 'day'),
            'source'         => 'FluentBooking'
        ];

        $existingUser = User::where('user_email', $authorEmail)->first();
        if ($existingUser) {
            $data['created_by'] = $existingUser->ID;
        }

        if (!$existingUser) {
            $data['settings']['author'] = [
                'name'          => $authorName,
                'email'         => $authorEmail,
                'subtask_count' => 0,
                'cover'         => [
                    'backgroundColor' => ''
                ]
            ];
        }

        $task = (new Task())->createTask($data);
        if (!$task) {
            return false;
        }

        do_action('fluent_boards/task_added_from_fluent_booking', $task, $booking, $calendarEvent, $feed);

        if ($position == 'top') {
            $task->moveToNewPosition(1);
        }

        foreach ($assignees as $assignee) {
            $task->addOrRemoveAssignee($assignee);
            $task->load('assignees');
            $task->updated_at = current_time('mysql');
            $task->save();

            $isEmailEnabled = (new NotificationService())->checkIfEmailEnable($assignee, Constant::BOARD_EMAIL_TASK_ASSIGN, $task->board_id);
            if ($isEmailEnabled) {
                (new TaskService())->sendMailAfterTaskModify('add_assignee', $assignee, $task->id);
            }
            
            do_action('fluent_boards/task_assignee_changed', $task, $assignee, 'added');
        }

        foreach ($boardLabels as $label) {
            $task->labels()->syncWithoutDetaching([$label => ['object_type' => Constant::OBJECT_TYPE_TASK_LABEL]]);
        }

        $taskUrl = admin_url("admin.php?page=fluent-boards#/boards/$boardId/tasks/{$task->id}");

        $this->addLog(
            $feed['settings']['name'],
            sprintf(__('Task has been created in FluentBoards. You can %s to view the task.',  'fluent-booking-pro'), '<a target="_blank" href="' . $taskUrl . '">' . __('click here', 'fluent-booking-pro') . '</a>'),
            $booking->id,
            'success'
        );
        return true;
    }
}
