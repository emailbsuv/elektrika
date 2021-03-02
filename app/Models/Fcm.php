<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Kreait\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Log;

class Fcm extends Model
{
    protected $_users;

    protected $_tokens;

    protected $_fcm_wrong_tokens;

    protected $_fcm_errors;

    protected $_report_for_log;

    protected $_report_for_response;

    protected $fillable = [
        'user_id', 'token',
    ];

    public function sendToMultiple($data)
    {
        $this->_prepareDataForMultiSend($data);
        $messaging = (new Firebase\Factory())->createMessaging();
        unset($data['users']);
        $message = CloudMessage::new()->withNotification(Notification::fromArray($data));

        try {
            $report = $messaging->sendMulticast($message, $this->_tokens);
            $this->_fcm_wrong_tokens = [];
            if ($report->hasFailures()) {
                foreach ($report->failures()->getItems() as $failure) {
                    $this->_fcm_wrong_tokens[] = $failure->target()->value();
                }
            }
            if ($report->hasFailures()) {
                foreach ($report->failures()->getItems() as $failure) {
                    $this->_fcm_errors[] = $failure->error()->getMessage();
                }
            }

            $this->_report_for_log = array_filter([
                'All sends'          => $report->successes()->count() + $report->failures()->count(),
                'Successful sends: ' => $report->successes()->count(),
                'Failed sends: '     => $report->failures()->count(),
                'Wrong tokens: '     => !empty($this->_fcm_wrong_tokens) ? json_encode($this->_fcm_wrong_tokens) : json_encode([])
            ]);

            $this->_report_for_response = array_filter([
                'All sends'          => $report->successes()->count() + $report->failures()->count(),
                'Successful sends: ' => $report->successes()->count(),
                'Failed sends: '     => $report->failures()->count(),
                'Wrong tokens: '     => !empty($this->_fcm_errors) ? $this->_fcm_errors : []
            ]);

        } catch (Firebase\Exception\MessagingException $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => $e->getMessage()], 401);
        } catch (Firebase\Exception\FirebaseException $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => $e->getMessage()], 401);
        }
        Log::info('Send push result:'.json_encode($this->_report_for_log));

        return $this;
    }

    protected function _prepareDataForMultiSend($data)
    {
        $this->_users = $data['users'];
        $this->_tokens = Arr::flatten(self::query()->whereIn('user_id', $this->_users)->select(['token'])->get()->toArray());

        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function getUsers()
    {
        return $this->_users;
    }

    public function getTokens()
    {
        return $this->_tokens;
    }

    public function getReportForLog()
    {
        return $this->_report_for_log;
    }

    public function getReportForResponse()
    {
        return $this->_report_for_response;
    }
}
