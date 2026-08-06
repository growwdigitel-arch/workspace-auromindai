<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\SmtpSetting\UpdateSmtpSetting;
use App\Models\EmailNotificationSetting;
use App\Models\SmtpSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TestEmail;

class SmtpSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.notificationSettings';
        $this->activeSettingMenu = 'notification_settings';
        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_notification_setting') !== 'all');

            return $next($request);
        });
    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    // phpcs:ignore
    public function update(UpdateSmtpSetting $request, $id)
    {
        // save all email notification settings
        $this->saveEmailNotificationSettings($request);

        $smtp = SmtpSetting::first();

        $data = $request->all();

        if ($request->mail_encryption == 'null') {
            $data['mail_encryption'] = null;
        }

        $smtp->update($data);
        
        // Skip automatic verification to prevent server crashes
        // Users can verify using "Send Test Email" button
        // $response = $smtp->verifySmtp();
        
        session(['smtp_setting' => $smtp]);
        session()->forget('email_notification_setting');

        // Always return success - verification happens via test email
        return Reply::success(__('messages.updateSuccess'));
    }

    public function saveEmailNotificationSettings($request)
    {
        EmailNotificationSetting::where('send_email', 'yes')->update(['send_email' => 'no']);

        if ($request->send_email) {
            EmailNotificationSetting::whereIn('id', $request->send_email)->update(['send_email' => 'yes']);
        }
    }

    public function showTestEmailModal()
    {
        return view('notification-settings.send-test-mail-modal', $this->data);
    }

    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email:rfc,strict',
        ]);

        $smtp = SmtpSetting::first();
        $response = $smtp->verifySmtp();

        if ($response['success']) {

            try {
                Notification::route('mail', \request()->test_email)->notify(new TestEmail());
            } catch (\Exception $e) {
                // Test email try catch
                return Reply::error($e->getMessage());
            }

            return Reply::success(__('messages.testMailSentSuccessfully'));
        }

        return Reply::error($response['message']);
    }

}
